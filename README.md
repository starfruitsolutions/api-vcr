# VCR (API Comparison Tool)

VCR is a command-line tool designed to record and playback API interactions, allowing for easy comparison between different API versions or implementations. It's particularly useful for testing API changes, migrations, or ensuring consistency across different environments.

## Features

- Record API interactions and save them as "tapes"
- Playback recorded interactions against a different API
- Compare responses, highlighting differences in:
  - Status codes
  - Headers
  - JSON body (with intelligent diffing)
- User-friendly CLI with mode selection and tape listing
- Colored output for easy identification of changes

## Requirements

- PHP 7.0 or higher
- PHP CLI

## Installation

1. Clone this repository or download the `vcr` script.
2. Make the script executable:
   ```
   chmod +x vcr
   ```
3. Optionally, move the script to a directory in your PATH for easy access.

## Usage

### Running the Tool

To start the VCR tool, simply run:

```
./vcr
```

### Recording Mode

1. Select "record" when prompted for the mode.
2. Enter the API URL you want to record (or press Enter for the default `http://localhost:8000`).
3. Provide a name for the tape.
4. The tool will start a recording server on `http://localhost:8080`.
5. Send your API requests to this address.
6. Press Ctrl+C to stop recording.

### Playback Mode

1. Select "playback" when prompted for the mode.
2. Enter the API URL you want to compare against.
3. You'll see a list of existing tapes. Select one by number or enter a new tape name.
4. The tool will replay the recorded requests against the new API and show the differences.

## Output

The tool provides a detailed comparison for each recorded interaction:

- URL and method of the request
- Detailed diff of any mismatches, including:
  - Added, removed, or changed JSON fields
  - Header differences
  - Status code changes

Differences are color-coded for easy identification:
- Green: Additions
- Red: Removals
- Yellow: Changes

## Tips

- Use descriptive names for your tapes to easily identify them later.
- When comparing APIs, ensure that any dynamic data (like timestamps or random IDs) are consistent or handled appropriately in your comparison logic.
- Review the `vcr_main_log.txt` file for additional debugging information if you encounter issues.
