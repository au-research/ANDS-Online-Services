package au.org.ands;

import java.util.concurrent.TimeUnit;

import org.openqa.selenium.WebDriver;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.firefox.FirefoxDriver;
import org.openqa.selenium.ie.InternetExplorerDriver;
import org.testng.Assert;

public class WebPageChecker {
	protected WebDriver driver;

	public void setUp(String drv, String drvPath) throws Exception {
		if (drv.equalsIgnoreCase("Chrome")) {
			System.setProperty("webdriver.chrome.driver", drvPath);
			this.driver = new ChromeDriver();
			this.driver.manage().timeouts().implicitlyWait(10, TimeUnit.SECONDS);
		} else if (drv.equalsIgnoreCase("Firefox"))
			this.driver = new FirefoxDriver();
		else if (drv.equalsIgnoreCase("IE"))
			this.driver = new InternetExplorerDriver();
		else
			Assert.fail("Cannot understand the browser!");
	}

	public void check(String url) {
		driver.get(url);
	}
}
