namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record StepUpGachaMaxLoopCount(int Value)
    {
        public static StepUpGachaMaxLoopCount Empty { get; } = new StepUpGachaMaxLoopCount(0);
        // データ設定がnullの場合はループ上限なしとするため、-1を無限ループの意味で使用する
        public static StepUpGachaMaxLoopCount Infinite { get; } = new StepUpGachaMaxLoopCount(-1);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        public bool IsInfinite()
        {
            return ReferenceEquals(this, Infinite);
        }
    }
}

