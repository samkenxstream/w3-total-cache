function requireRoot(p) {
	return require('../../' + p);
}

const expect = require('chai').expect;
const log = require('mocha-logger');
const util = require('util');
const exec = util.promisify(require('child_process').exec);

const dom = requireRoot('lib/dom');
const env = requireRoot('lib/environment');
const sys = requireRoot('lib/sys');
const w3tc = requireRoot('lib/w3tc');
const wp = requireRoot('lib/wp');

/**environments: environments('blog') */

async function testHeaders(expectation) {
	for (let type in testUrls) {
		log.log(`${type} - ${testUrls[type]}`);
		let response = await page.goto(testUrls[type], {waitUntil: 'domcontentloaded'});

		let headers = response.headers();
		if (expectation[type]) {
			expect(headers.etag).is.not.empty;
		} else {
			expect(headers.etag).is.undefined;
		}
	}
}



let testPageUrl;
let testUrls;

describe('', function() {
	this.timeout(sys.suiteTimeout);
	before(sys.beforeDefault);
	after(sys.after);



	it('copy theme files', async() => {
		let theme = await wp.getCurrentTheme(adminPage);
		let targetPath = env.wpContentPath + 'themes/' + theme + '/qa';
		await sys.copyPhpToPath('../../plugins/browsercache/*', targetPath);
	});



	it('set options', async() => {
		await w3tc.setOptions(adminPage, 'w3tc_general', {
	      pgcache__enabled: true,
	      browsercache__enabled: true,
	      pgcache__engine: 'file_generic'
	    });

	    await w3tc.setOptions(adminPage, 'w3tc_browsercache', {
	      browsercache_replace_exceptions: '.*\.css',
	      browsercache__cssjs__etag: true,
	      browsercache__html__etag: true,
	      browsercache__other__etag: true
	    });

	    await sys.afterRulesChange();
	});



	it('create test page', async() => {
		let testPage = await wp.postCreate(adminPage, {
			type: 'page',
			title: 'test',
			content: 'page content',
			template: 'qa/basic.php'
		});
		testPageUrl = testPage.url;
	});

	it('collect assets', async() => {
		let response = await page.goto(testPageUrl, {waitUntil: 'domcontentloaded'});
		testUrls = {};
			//html: testPageUrl
		//};

		testUrls['other'] = await page.$eval('#image', (e) => e.src);

		let scripts = await dom.listScriptSrc(page);
		testUrls['cssjs'] = scripts[0];
	});

	it('etag is present', async() => {
		await testHeaders({
			html: true,
			other: true,
			cssjs: true
		});
	});

	it('set options - etag off', async() => {
	    await w3tc.setOptions(adminPage, 'w3tc_browsercache', {
	      browsercache__cssjs__etag: false,
	      browsercache__html__etag: false,
	      browsercache__other__etag: false
	    });

	    await sys.afterRulesChange();
	});

	it('etag is not present', async() => {
		await testHeaders({
			html: false,
			other: false,
			cssjs: false
		});
	});

});
