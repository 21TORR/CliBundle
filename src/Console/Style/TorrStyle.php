<?php declare(strict_types=1);

namespace Torr\Cli\Console\Style;

use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * 21TORR-branded CLI style
 */
class TorrStyle extends SymfonyStyle
{
	private const HIGHLIGHT = "red";

	/**
	 * @inheritDoc
	 */
	public function title (string $message) : void
	{
		$length = Helper::width(Helper::removeDecoration($this->getFormatter(), $message)) + 4;

		$this->newLine();
		$this->writeln(sprintf(' <fg=%s>╭%s╮</>', self::HIGHLIGHT, str_repeat("─", $length)));
		$this->writeln(sprintf(' <fg=%s>│</>  %s  <fg=red>│</>', self::HIGHLIGHT, $message));
		$this->writeln(sprintf(' <fg=%s>╰%s╯</>', self::HIGHLIGHT, str_repeat("─", $length)));
		$this->newLine();
	}

	/**
	 * @inheritDoc
	 */
	public function section (string $message) : void
	{
		$length = Helper::width(Helper::removeDecoration($this->getFormatter(), $message));

		$this->newLine();
		$this->writeln([
			$message,
			sprintf('<fg=%s>%s</>', self::HIGHLIGHT, str_repeat("─", $length)),
		]);
		$this->newLine();
	}

	/**
	 * @inheritDoc
	 *
	 * @param string[]                                         $headers
	 * @param list<list<scalar|TableCell|null>|TableSeparator> $rows
	 */
	public function table (array $headers, array $rows) : void
	{
		$style = (new TableStyle())
			->setHorizontalBorderChars('─')
			->setVerticalBorderChars('│')
			->setCrossingChars('┼', '╭', '┬', '╮', '┤', '╯', '┴', '╰', '├');
		$style->setCellHeaderFormat('<info>%s</>');

		$table = new Table($this);
		$table->setHeaders($headers);
		$table->setRows($rows);
		$table->setStyle($style);

		$table->render();
		$this->newLine();
	}

	/**
	 * @inheritDoc
	 *
	 * @param string[] $elements
	 */
	public function listing (array $elements) : void
	{
		$this->newLine();
		$elements = array_map(
			static fn ($element) => sprintf('  <fg=%s>●</> %s', self::HIGHLIGHT, $element),
			$elements,
		);

		$this->writeln($elements);
		$this->newLine();
	}

	/**
	 * @inheritDoc
	 */
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
	 * A smaller way to mark something as done
	 */
	public function done (string $message) : void
	{
		$this->write(\sprintf(
			"<fg=green>✓</> %s",
			$message,
		));
	}
}
