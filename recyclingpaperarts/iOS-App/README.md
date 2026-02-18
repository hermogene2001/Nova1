# Recycling Paper Arts iOS App

A SwiftUI-based iOS application that fetches and displays content from the Recycling Paper Arts website (https://recyclingpaperarts.com/).

## Features

- **Web Content Fetching**: Automatically retrieves content from recyclingpaperarts.com
- **Clean UI**: Modern SwiftUI interface with intuitive navigation
- **Content Cards**: Display content in easily readable card format
- **Detail View**: Tap any content to see more details
- **Web Links**: Direct links to website articles and projects
- **Pull-to-Refresh**: Update content with a simple pull gesture
- **Error Handling**: Graceful error messages and retry functionality
- **Image Support**: Display images from articles when available
- **Responsive Design**: Works on all iPhone sizes and iPad

## Requirements

- iOS 15.0 or later
- Xcode 14.0 or later
- Swift 5.7 or later

## Installation

1. Open `RecyclingPaperArts.xcodeproj` in Xcode
2. Select your target device or simulator
3. Press `Cmd + R` to build and run

## Project Structure

```
RecyclingPaperArts/
├── RecyclingPaperArtsApp.swift      # App entry point
├── ContentView.swift                 # Main UI and list view
├── NetworkManager.swift              # Network requests and HTML parsing
├── WebContent.swift                  # Data models and view models
├── Info.plist                        # App configuration
└── Assets.xcassets                   # App images and icons
```

## Architecture

### MVVM Pattern
- **Model**: `WebContent` - represents individual content items
- **ViewModel**: `WebContentViewModel` - manages content loading and state
- **View**: `ContentView`, `ContentCardView`, `ContentDetailView` - SwiftUI views

### Key Components

#### NetworkManager
Handles all network requests and HTML parsing:
- Fetches HTML content from recyclingpaperarts.com
- Parses HTML to extract titles, descriptions, and metadata
- Implements error handling and retry logic
- Custom user-agent to ensure proper website access

#### WebContent Model
Represents a single piece of content with:
- Unique identifier (UUID)
- Title and description
- Source URL
- Optional image URL
- Date added timestamp

#### ContentView
Main user interface with:
- List of content cards
- Loading state with progress indicator
- Error state with retry button
- Refresh functionality
- Navigation to detail views

## Usage

### Fetching Content
The app automatically fetches content when it appears. You can also:
- Pull down to refresh the content list
- Tap the refresh button in the navigation bar
- Tap "Try Again" if an error occurs

### Viewing Content Details
1. Tap on any content card
2. View the full title, description, and any available images
3. Tap "Visit Website" to open the article in Safari

## API

### NetworkManager Methods

```swift
func fetchWebsite(from urlString: String, 
                 completion: @escaping (Result<[WebContent], NetworkError>) -> Void)
```

Fetches and parses website content.

**Parameters:**
- `urlString`: The URL to fetch (e.g., "https://recyclingpaperarts.com/")
- `completion`: Closure with `Result<[WebContent], NetworkError>`

**Returns:** Array of `WebContent` objects or `NetworkError`

## Error Handling

The app handles several error types:

- **Invalid URL**: Malformed URL provided
- **Network Error**: Connection or network-related failures
- **HTTP Error**: Server returned error status code
- **Decoding Error**: Failed to parse response data

All errors are displayed to the user with appropriate messages and a retry option.

## Configuration

### Network Settings
- **Timeout**: 30 seconds
- **User-Agent**: Custom mobile Safari user-agent
- **Transport Security**: Allows HTTP and HTTPS connections to recyclingpaperarts.com

### Supported Orientations
- Portrait
- Landscape Left
- Landscape Right
- iPad: All orientations

## Performance Considerations

- **Lazy Loading**: Uses `LazyVStack` for efficient list rendering
- **Async Images**: Images load asynchronously without blocking UI
- **Caching**: URLSession automatically caches responses
- **Background Operations**: Network requests run on background thread

## Future Enhancements

- [ ] Local caching of fetched content
- [ ] Offline mode support
- [ ] Content filtering and search
- [ ] Bookmarking favorite articles
- [ ] Share content functionality
- [ ] Dark mode optimization
- [ ] Widget support
- [ ] Notifications for new content

## Troubleshooting

### App Not Loading Content
- Check internet connection
- Verify recyclingpaperarts.com is accessible
- Try refreshing with the refresh button
- Check Console for network error details

### Images Not Displaying
- Verify image URLs are valid
- Check internet connection quality
- Some images may not be available on the website

### Build Errors
- Ensure iOS 15.0+ deployment target
- Update Xcode to latest version
- Clean build folder (Cmd + Shift + K)

## License

This project is provided as-is for use with recyclingpaperarts.com content.

## Support

For issues or questions about the app, please ensure:
1. The website is accessible
2. Your iOS device is updated
3. You have a stable internet connection

## Version History

### Version 1.0 (Initial Release)
- Basic content fetching and display
- SwiftUI-based UI
- HTML parsing for content extraction
- Error handling and retry logic
