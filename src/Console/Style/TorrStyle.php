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
	 *
	 */
	#[\Override]
	public function info (array|string $message) : void
	{
		$this->block(
			$message,
			"INFO",
			'fg=white;bg=blue',
			' ',
			true,
		);
	}

	/**
	 * A smaller way to mark something as done
	 */
	public function done (string $message) : void
	{
		$this->write(\sprintf(
			"<fg=green>✓</> %s",
			$message,
		));
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
