# WP-HistographAI

Revive any year in history with OpenAI-powered summaries on WordPress. This plugin fetches significant worldwide events for a given year using the power of OpenAI's GPT-3.5 (or 4) model and displays them in a table format on your WordPress website.

## Features:

1. **OpenAI Integration:** Use the OpenAI API to fetch historical summaries.
2. **Scheduled Events:** Use Action Scheduler to fetch historical data in the background, ensuring smooth website performance.
3. **Admin Interface:** A dedicated settings page to input your OpenAI API key.
4. **Shortcode Integration:** A shortcode to display the HistographAI form and results on any post or page.
5. **Social Sharing:** Integrated social sharing options for fetched histories, enhancing user engagement.

![314-WP-HistographAI-OpenAI-powered-Summaries-on-WordPress.jpg](img%2F314-WP-HistographAI-OpenAI-powered-Summaries-on-WordPress.jpg)

## Installation:

1. Upload all files to the `/wp-content/plugins/wp-histographai/` directory of your WordPress setup.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to the WP-HistographAI settings page under the WordPress settings menu and input your OpenAI API key.
4. Use the shortcode `[histographai_form]` in your posts or pages to display the HistographAI form.
5. Navigate to the plugin's directory and run `composer install` to install the required PHP packages. Ensure the `vendor` directory is generated.

## Usage:

1. After activating the plugin and setting up the OpenAI API key, use the shortcode to display the form.
2. The user can select a year from the dropdown and click on "Get Summary" to fetch the historical summary.
3. The results will be displayed below the form, and social sharing links will be generated for easy sharing.


### API Key Configuration:

To ensure the plugin can communicate with the OpenAI API, you must provide your OpenAI API key. This can be done through the WP-HistographAI settings page in your WordPress admin dashboard:

1. Navigate to the WP-HistographAI settings page.
2. Enter your OpenAI API key in the provided field.
3. Save your settings.

## Social Sharing:

The plugin provides users with easy-to-use social sharing buttons for Facebook, Twitter, and LinkedIn. This enhances user engagement and allows users to share the fascinating historical summaries with their network.

## Typewriting Table Effect:

WP-HistographAI introduces a captivating visual feature: the typewriting table effect. This effect gives users the impression that the historical summary is being typed out in real-time, adding an interactive and dynamic aspect to the presentation. Here's a breakdown of how it works:

1. **Initial Display**: When a year's summary is fetched and displayed, all table cells are initially empty with their content hidden.

2. **Sequential Typing**: The content of each table cell is typed out character by character in sequence. This simulates the effect of a typewriter, enhancing user engagement.

3. **JavaScript Implementation**: The effect is achieved using custom JavaScript, specifically within the `typeTableContent()` function in the `ajax.js` file. The function identifies each table cell and types out its content with a slight delay between each character.

4. **Enhanced User Experience**: The typewriting effect, while purely aesthetic, provides a unique and memorable user experience. It draws attention to the content and makes the exploration of historical events feel more interactive and immersive.

5. **Customization**: For those who wish to adjust the speed or behavior of the typewriting effect, they can do so by modifying the related JavaScript functions in the `ajax.js` file.

## Preview:

![314-WP-HistographAI-OpenAI-powered-Summaries.gif](img%2F314-WP-HistographAI-OpenAI-powered-Summaries.gif)

---

## Technical details

### Constants Configuration:

The plugin makes use of several constants to configure its behavior:

1. **`HISTOGRAPHAI_YEAR_COUNT`**: Determines the number of years to process in each scheduled event.
2. **`HISTOGRAPHAI_YEAR_START`** and **`HISTOGRAPHAI_YEAR_END`**: Define the range of years available for processing.
3. **`GPT_PROMPT`**: The prompt used for querying the OpenAI API.
4. **`HISTOGRAPHAI_RECURRENCE`**: Frequency of the scheduled events, possible values are 'daily', 'weekly', or 'monthly'.
5. **`HISTOGRAPHAI_SCHEDULED_HOUR`** and **`HISTOGRAPHAI_SCHEDULED_MINUTE`**: Define the hour and minute for the scheduled event.

These constants can be modified directly within the `wp-histographai.php` file to adjust the plugin's behavior as needed.


### Background Jobs & Action Scheduler:

WP-HistographAI uses the Action Scheduler library to handle the fetching of historical data in the background. Instead of fetching data in real-time (which could slow down the website), the plugin schedules jobs to fetch data at specific intervals. This ensures that the data is ready when the user requests it and provides a smooth user experience.

### Dependencies:

This plugin requires the following libraries (as specified in `composer.json`):

- openai-php/client
- symfony/http-client
- nyholm/psr7
- guzzlehttp/guzzle
- woocommerce/action-scheduler
- erusev/parsedown

### OpenAI API Integration:

WP-HistographAI leverages the power of OpenAI's GPT-3.5 model to generate historical summaries. Here's how the integration works:

1. **API Endpoint**: The plugin communicates with OpenAI's endpoint dedicated to their GPT-3.5 model. This ensures that the responses are powered by one of the most advanced language models available.

2. **Prompt Configuration**: The constant `GPT_PROMPT` is used as a base to craft queries to the API. By default, it's set to `List the most significant worldwide events in a table format for year`, but this can be modified to adjust the nature of the historical summaries.

3. **API Key**: The OpenAI API requires an API key for authentication. This key needs to be provided in the WP-HistographAI settings page. All requests to the OpenAI API use this key.

### Efficient Data Handling and Storage:

WP-HistographAI is designed to be resource-efficient and minimize the reliance on external API calls. Here's how the plugin manages data:

1. **WordPress Database Storage**: Once a historical summary for a specific year is fetched from the OpenAI API, the data is stored in the WordPress database. This means that for any subsequent requests for the same year, the plugin retrieves the data directly from the database rather than making another API call.

2. **Minimizing API Calls**: The plugin is designed to connect to the OpenAI API only when adding a new year item. If a summary for a particular year is already available in the database, the plugin will not make redundant API requests for that year. This not only speeds up the retrieval process for the end-user but also helps in reducing potential costs associated with the API.

3. **Data Structure**: The historical summaries are saved as custom post types (`histographai_year`) in the WordPress database. This approach makes it easy to manage, query, and display the data using native WordPress functions.

4. **Background Jobs**: With the integration of the Action Scheduler, the plugin fetches data in the background. This means that the frontend user experience remains smooth and uninterrupted, while the backend works to update the database with new summaries as required.

### Displaying Markdown Content on the Frontend:

WP-HistographAI supports the use of markdown to structure and format the historical summaries. Using markdown allows for a consistent and readable format when presenting data, making it easier for users to digest complex information. Here's how markdown content is managed and displayed:

1. **Parsedown Integration**: The plugin leverages the Parsedown library, a popular PHP parser for markdown. It converts markdown text into HTML, ensuring that the content is displayed correctly on the frontend.

2. **Storing Content**: When a historical summary is fetched from the OpenAI API, it's stored in the WordPress database in markdown format. This provides a clear and structured way to save the data.

3. **Rendering on Frontend**: When a user requests a summary for a specific year, the markdown content is retrieved from the database and then passed through the Parsedown parser. The resultant HTML is then rendered on the frontend, preserving all the formatting, lists, headings, and other markdown elements.
