import SwiftUI

struct ContentView: View {
    @StateObject private var viewModel = WebContentViewModel()
    @State private var selectedContent: WebContent?
    
    var body: some View {
        NavigationView {
            ZStack {
                if viewModel.isLoading {
                    VStack(spacing: 20) {
                        ProgressView()
                            .scaleEffect(1.5)
                        Text("Loading content...")
                            .font(.headline)
                            .foregroundColor(.gray)
                    }
                } else if let errorMessage = viewModel.errorMessage {
                    VStack(spacing: 16) {
                        Image(systemName: "exclamationmark.triangle.fill")
                            .font(.system(size: 48))
                            .foregroundColor(.orange)
                        
                        Text("Error Loading Content")
                            .font(.headline)
                        
                        Text(errorMessage)
                            .font(.caption)
                            .foregroundColor(.gray)
                            .multilineTextAlignment(.center)
                        
                        Button(action: {
                            viewModel.fetchWebContent()
                        }) {
                            Text("Try Again")
                                .frame(maxWidth: .infinity)
                                .padding()
                                .background(Color.blue)
                                .foregroundColor(.white)
                                .cornerRadius(8)
                        }
                    }
                    .padding()
                } else if viewModel.contents.isEmpty {
                    VStack(spacing: 16) {
                        Image(systemName: "doc.text.fill")
                            .font(.system(size: 48))
                            .foregroundColor(.blue)
                        
                        Text("No Content Available")
                            .font(.headline)
                        
                        Text("Pull to refresh or tap the button below")
                            .font(.caption)
                            .foregroundColor(.gray)
                        
                        Button(action: {
                            viewModel.fetchWebContent()
                        }) {
                            Text("Load Content")
                                .frame(maxWidth: .infinity)
                                .padding()
                                .background(Color.blue)
                                .foregroundColor(.white)
                                .cornerRadius(8)
                        }
                    }
                    .padding()
                } else {
                    ScrollView {
                        LazyVStack(spacing: 12) {
                            ForEach(viewModel.contents) { content in
                                NavigationLink(destination: ContentDetailView(content: content)) {
                                    ContentCardView(content: content)
                                }
                            }
                        }
                        .padding()
                    }
                    .refreshable {
                        await refreshContent()
                    }
                }
            }
            .navigationTitle("Recycling Paper Arts")
            .toolbar {
                ToolbarItem(placement: .navigationBarTrailing) {
                    Button(action: {
                        viewModel.fetchWebContent()
                    }) {
                        Image(systemName: "arrow.clockwise")
                    }
                }
            }
        }
        .onAppear {
            viewModel.fetchWebContent()
        }
    }
    
    private func refreshContent() async {
        return await withCheckedContinuation { continuation in
            viewModel.fetchWebContent()
            DispatchQueue.main.asyncAfter(deadline: .now() + 1.0) {
                continuation.resume()
            }
        }
    }
}

struct ContentCardView: View {
    let content: WebContent
    
    var body: some View {
        VStack(alignment: .leading, spacing: 8) {
            HStack(spacing: 12) {
                VStack(alignment: .leading, spacing: 8) {
                    Text(content.title)
                        .font(.headline)
                        .lineLimit(2)
                        .foregroundColor(.primary)
                    
                    Text(content.description)
                        .font(.caption)
                        .lineLimit(3)
                        .foregroundColor(.secondary)
                    
                    HStack {
                        Image(systemName: "globe")
                            .font(.caption)
                            .foregroundColor(.blue)
                        
                        Text("recyclingpaperarts.com")
                            .font(.caption2)
                            .foregroundColor(.blue)
                    }
                }
                
                Spacer()
                
                if let imageURL = content.imageURL {
                    AsyncImage(url: URL(string: imageURL)) { image in
                        image
                            .resizable()
                            .scaledToFill()
                    } placeholder: {
                        Image(systemName: "photo.fill")
                            .foregroundColor(.gray)
                    }
                    .frame(width: 80, height: 80)
                    .cornerRadius(8)
                    .clipped()
                } else {
                    Image(systemName: "leaf.fill")
                        .font(.system(size: 32))
                        .foregroundColor(.green)
                        .frame(width: 80, height: 80)
                        .background(Color(.systemGray6))
                        .cornerRadius(8)
                }
            }
            
            Divider()
        }
        .padding()
        .background(Color(.systemBackground))
        .cornerRadius(12)
        .shadow(radius: 2)
    }
}

struct ContentDetailView: View {
    let content: WebContent
    @Environment(\.presentationMode) var presentationMode
    
    var body: some View {
        ScrollView {
            VStack(alignment: .leading, spacing: 16) {
                VStack(alignment: .leading, spacing: 12) {
                    Text(content.title)
                        .font(.title2)
                        .fontWeight(.bold)
                    
                    HStack {
                        Image(systemName: "calendar")
                            .font(.caption)
                        Text(content.dateAdded.formatted(date: .abbreviated, time: .omitted))
                            .font(.caption)
                            .foregroundColor(.secondary)
                    }
                }
                
                if let imageURL = content.imageURL {
                    AsyncImage(url: URL(string: imageURL)) { image in
                        image
                            .resizable()
                            .scaledToFit()
                    } placeholder: {
                        ProgressView()
                    }
                    .cornerRadius(12)
                }
                
                Text(content.description)
                    .font(.body)
                    .lineSpacing(2)
                
                VStack(spacing: 12) {
                    Link(destination: URL(string: content.url) ?? URL(string: "https://recyclingpaperarts.com/")!) {
                        HStack {
                            Image(systemName: "globe")
                            Text("Visit Website")
                            Spacer()
                            Image(systemName: "arrow.up.right")
                        }
                        .frame(maxWidth: .infinity)
                        .padding()
                        .background(Color.blue)
                        .foregroundColor(.white)
                        .cornerRadius(8)
                    }
                }
                
                Spacer()
            }
            .padding()
        }
        .navigationBarTitleDisplayMode(.inline)
    }
}

#Preview {
    ContentView()
}
