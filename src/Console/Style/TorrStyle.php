<?php declare(strict_types=1);

namespace Torr\Cli\Console\Style;

use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Terminal;

/**
 * 21TORR-branded CLI style
 */
class TorrStyle extends SymfonyStyle
{
	private const HIGHLIGHT = "red";

	/**
	 */
	#[\Override]
	public function title (string $message) : void
	{
		$length = Helper::width(Helper::removeDecoration($this->getFormatter(), $message)) + 4;

		$this->newLine();
		$this->writeln(\sprintf(' <fg=%s>╭%s╮</>', self::HIGHLIGHT, str_repeat("─", $length)));
		$this->writeln(\sprintf(' <fg=%s>│</>  %s  <fg=red>│</>', self::HIGHLIGHT, $message));
		$this->writeln(\sprintf(' <fg=%s>╰%s╯</>', self::HIGHLIGHT, str_repeat("─", $length)));
		$this->newLine();
	}

	/**
	 *
	 */
	public function headline (string $message) : void
	{
		$length = Helper::width(Helper::removeDecoration($this->getFormatter(), $message));

		$this->writeln([
			"",
			\sprintf(
				"<fg=red>────</> %s %s",
				$message,
				"<fg=red>" . str_repeat("─", $this->getLineLength() - $length - 6) . "</>",
			),
			"",
		]);
	}

	/**
	 */
	#[\Override]
	public function section (string $message) : void
	{
		$length = Helper::width(Helper::removeDecoration($this->getFormatter(), $message));

		$this->newLine();
		$this->writeln([
			$message,
			\sprintf('<fg=%s>%s</>', self::HIGHLIGHT, str_repeat("─", $length)),
		]);
		$this->newLine();
	}

	/**
	 * @param string[]                                         $headers
	 * @param list<list<scalar|TableCell|null>|TableSeparator> $rows
	 */
	#[\Override]
	public function table (array $headers, array $rows) : void
	{
		$this->createTable()
			->setHeaders($headers)
			->setRows($rows)
			->render();
		$this->newLine();
	}

	/**
	 *
	 */
	#[\Override]
	public function createTable () : Table
	{
		$table = parent::createTable();

		$style = (new TableStyle())
			->setHorizontalBorderChars('─')
			->setVerticalBorderChars('│')
			->setCrossingChars('┼', '╭', '┬', '╮', '┤', '╯', '┴', '╰', '├');
		$style->setCellHeaderFormat('<info>%s</>');

		return $table->setStyle($style);
	}

	/**
	 * @param string[] $elements
	 */
	#[\Override]
	public function listing (array $elements) : void
	{
		$this->newLine();
		$elements = array_map(
			static fn ($element) => \sprintf('  <fg=%s>●</> %s', self::HIGHLIGHT, $element),
			$elements,
		);

		$this->writeln($elements);
		$this->newLine();
	}

	/**
	 */
	#[\Override]
	public function createProgressBar (
		int $max = 0,
		string $format = " %current%/%max% [%bar%] %percent:3s%% %elapsed:6s% %message%",
	) : ProgressBar
	{
		$progressBar = parent::createProgressBar($max);
		$progressBar->setFormat($format);

		return $progressBar;
	}

	/**
	 * @param string[]|string $message
	 */
	#[\Override]
	public function info (array|string $message) : void
	{
		$this->renderAdmonition($message, "💡", "black", "blue");
	}

	/**
	 * @param string[]|string $message
	 */
	#[\Override]
	public function warning (array|string $message) : void
	{
		$this->renderAdmonition($message, "🚨", "black", "#FFD700");
	}

	/**
	 * @param string[]|string $message
	 */
	#[\Override]
	public function caution (array|string $message) : void
	{
		$this->renderAdmonition($message, "🚧", "black", "#FF8700");
	}

	/**
	 * @param string[]|string $message
	 */
	#[\Override]
	public function error (array|string $message) : void
	{
		$this->renderAdmonition($message, "🔴", "black", "red");
	}

	/**
	 * @param string[]|string $message
	 */
	#[\Override]
	public function note (array|string $message) : void
	{
		$this->renderAdmonition($message, "📝", "black", "#CCCCCC");
	}

	/**
	 * Renders a styled admonition box with rounded corners and solid background fill.
	 *
	 * Each line is wrapped in the style tag so the background color covers the full
	 * content width, producing a solid colored band with rounded corners:
	 *
	 *   ╭──────────────────────────────────────────────╮
	 *   │ LABEL  Message text padded to fill the line  │
	 *   ╰──────────────────────────────────────────────╯
	 *
	 * Line anatomy (total = lineLength):
	 *   border:  ╭ + {innerWidth + 2 dashes} + ╮             = innerWidth + 3
	 *   content: │ + space + {innerWidth chars} + space + │   = innerWidth + 4
	 * Both are preceded by one leading space → innerWidth = lineLength - 5
	 */
	private function renderAdmonition (
		array|string $message,
		string $label,
		string $foreground,
		string $background,
	) : void
	{
		$messages = array_values(array_filter((array) $message));
		$lineLength = $this->getLineLength();

		// Inner content area width that makes each line exactly lineLength chars.
		// {paddingH} + {innerWidth} + {paddingH} = innerWidth + paddingH * 2
		$innerWidth = $lineLength - 6;

		// "LABEL  " prefix on the first line; subsequent lines indented to match
		$labelPrefix = $label . '  ';
		$labelPrefixLen = mb_strwidth($labelPrefix);
		$textWidth = max(1, $innerWidth - $labelPrefixLen);
		$indent = str_repeat(' ', $labelPrefixLen);

		$contentLines = [];

		foreach ($messages as $i => $msg)
		{
			foreach (explode("\n", wordwrap($msg, $textWidth, "\n", true)) as $j => $line)
			{
				$contentLines[] = (0 === $i && 0 === $j ? $labelPrefix : $indent) . $line;
			}
		}

		// Each line is fully wrapped in the style tag so the background color fills
		// every character cell — spaces become the solid fill.
		// Width: {paddingH} + {innerWidth} + {paddingH} = innerWidth + paddingH * 2 = lineLength
		$paddingH = 2;
		$tag = "fg={$foreground};bg={$background}";
		$blank = \sprintf('<%s>%s</>', $tag, str_repeat(' ', $innerWidth + $paddingH * 2));

		$this->newLine();
		$this->writeln($blank);

		foreach ($contentLines as $line)
		{
			$padding = str_repeat(' ', max(0, $innerWidth - mb_strwidth($line)));
			$horizontalPad = str_repeat(' ', $paddingH);
			$this->writeln(\sprintf('<%s>%s%s%s%s</>', $tag, $horizontalPad, $line, $padding, $horizontalPad));
		}

		$this->writeln($blank);
		$this->newLine();
	}

	/**
	 */
	#[\Override]
	public function comment (array|string $message) : void
	{
		$this->block($message, null, null, ' <fg=cyan>//</> ', false, false);
	}

	/**
	 * A smaller way to mark something as done
	 *
	 * @param string[]|string $message
	 */
	public function done (array|string $message = "done") : void
	{
		$this->block($message, null, null, ' <fg=green>✓</> ', false, false);
	}

	/**
	 * Calculates the line length (= width) of the CLI
	 */
	private function getLineLength (
		int $maxLineLength = 250,
	) : int
	{
		$width = new Terminal()->getWidth() ?: $maxLineLength;

		return min($width - (int) (\DIRECTORY_SEPARATOR === '\\'), $maxLineLength);
	}
}
