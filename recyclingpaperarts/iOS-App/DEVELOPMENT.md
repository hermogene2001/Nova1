# Recycling Paper Arts iOS App - Development Guide

## Quick Start

### Prerequisites
- **Xcode 14.0+** - Download from App Store or [developer.apple.com](https://developer.apple.com/)
- **macOS 12.0+** - Compatible version
- **iOS 15.0+** - Target deployment version

### First Run

1. **Open the Project**
   ```bash
   cd iOS-App
   open RecyclingPaperArts.xcodeproj
   ```

2. **Select Target**
   - Choose your target simulator or connected device
   - Recommended: iPhone 14 Pro or iPad

3. **Build & Run**
   - Press `Cmd + R` or Product → Run
   - App will launch with fetching enabled

## App Architecture

### Files Overview

```
RecyclingPaperArts/
├── RecyclingPaperArtsApp.swift      # App entry point (@main)
├── ContentView.swift                 # Main UI with list and details
├── NetworkManager.swift              # Network layer and HTML parsing
├── WebContent.swift                  # Models and ViewModels
├── Info.plist                        # App configuration
└── Assets.xcassets/                  # Images and icons
```

### Data Flow

```
1. App Launch
   ↓
2. ContentView appears
   ↓
3. onAppear → viewModel.fetchWebContent()
   ↓
4. NetworkManager.fetchWebsite()
   ↓
5. Parse HTML → Create [WebContent]
   ↓
6. Update @Published contents
   ↓
7. View refreshes with content
```

## Key Features

### Content Fetching
- **Automatic**: Fetches on app launch
- **Manual**: Tap refresh button or pull to refresh
- **Automatic Retry**: Failed requests can be retried

### Content Display
- **Card Layout**: Easy to scan and browse
- **Detail View**: Full content with images and links
- **Navigation**: Seamless transition between views

### Error Handling
- Network errors with retry option
- Loading states with progress indicator
- Empty state guidance

## Development Tasks

### Adding a New Feature

1. **Modify NetworkManager.swift** for backend changes
2. **Update WebContent.swift** for data model changes
3. **Edit ContentView.swift** for UI changes
4. **Test** with different network conditions

### Customizing HTML Parsing

Edit the `parseHTML(_ htmlString: String)` method in NetworkManager.swift:

```swift
private func parseHTML(_ htmlString: String) -> [WebContent] {
    var contents: [WebContent] = []
    
    // Add custom parsing logic here
    // Use regex patterns to extract specific elements
    
    return contents
}
```

### Adding Images to Content

Update the `WebContent` model creation with image URLs:

```swift
let content = WebContent(
    title: "Article Title",
    description: "Description",
    url: "https://example.com",
    imageURL: "https://example.com/image.jpg"  // Add this
)
```

## Testing

### Manual Testing Checklist

- [ ] App loads without crashes
- [ ] Content fetches successfully
- [ ] Refresh button works
- [ ] Pull-to-refresh works
- [ ] Detail view displays correctly
- [ ] Web links open in Safari
- [ ] Error handling shows proper messages
- [ ] Loading indicator appears during fetch

### Network Testing

Test different network conditions:

1. **Good Connection**: Everything should work smoothly
2. **Slow Connection**: Loading indicator should show
3. **No Connection**: Error message should display
4. **Timeout**: After 30 seconds, error should show

## Debugging

### Enable Console Logging

Add print statements in NetworkManager:

```swift
print("Fetching: \(urlString)")
print("Parsed \(contents.count) items")
print("Error: \(error.localizedDescription)")
```

### Check Network Activity

Use Xcode's Network Link Conditioner:
1. Download from Additional Tools (Xcode → More → Additional Tools)
2. Install and configure
3. Simulate different network speeds

### Safari Web Inspector

Debug JavaScript issues on the website:
1. Open Safari on target device
2. Device → Develop → Web Inspector
3. Test website loading directly

## Deployment

### Prepare for App Store

1. **Update Version Number**
   - Edit `CFBundleShortVersionString` in Info.plist

2. **Update App Icon**
   - Add icons to Assets.xcassets → AppIcon

3. **Set Bundle Identifier**
   - Use unique identifier (e.g., com.yourcompany.recyclingpaperarts)

4. **Configure Signing**
   - Select team in Signing & Capabilities

5. **Archive App**
   - Product → Archive
   - Validate and upload through App Store Connect

## Performance Optimization

### Current Optimizations

- **Lazy Loading**: LazyVStack renders only visible items
- **Async Images**: Images load without blocking UI
- **Background Threads**: Network requests run in background
- **URL Caching**: URLSession caches responses automatically

### Further Improvements

1. **Local Storage**: Save fetched content using CoreData
2. **Image Caching**: Cache downloaded images
3. **Pagination**: Load content in batches
4. **Background Refresh**: Update content periodically

## Troubleshooting

### Build Issues

**Problem**: "Module 'X' not found"
- **Solution**: Product → Build Folder Cleanup (Cmd + Shift + K)

**Problem**: "Code signing error"
- **Solution**: Select correct Team in Signing & Capabilities

**Problem**: "Target deployment is below minimum"
- **Solution**: Update iOS deployment target to 15.0+

### Runtime Issues

**Problem**: Content doesn't load
- **Solution**: Check internet connection and website availability

**Problem**: Images don't display
- **Solution**: Verify image URLs are accessible

**Problem**: App crashes on launch
- **Solution**: Check Console for error messages and stack trace

## Resources

- [SwiftUI Documentation](https://developer.apple.com/documentation/swiftui)
- [URLSession Guide](https://developer.apple.com/documentation/foundation/urlsession)
- [Xcode Documentation](https://developer.apple.com/documentation/xcode)
- [App Store Guidelines](https://developer.apple.com/app-store/review/guidelines/)

## Support & Contribution

For issues:
1. Check the troubleshooting section
2. Review the code comments
3. Check Xcode Console for error details
4. Verify website is accessible

## Version Info

- **App Version**: 1.0
- **Minimum iOS**: 15.0
- **Xcode**: 14.0+
- **Swift**: 5.7+
