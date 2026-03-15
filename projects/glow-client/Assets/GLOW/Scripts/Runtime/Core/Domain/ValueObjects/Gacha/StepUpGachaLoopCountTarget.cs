namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record StepUpGachaLoopCountTarget(int Value)
    {
        // データ設定で0を使用するため、-2をEmptyの意味で使用する
        public static StepUpGachaLoopCountTarget Empty { get; } = new(-2);
        // データ設定がnullの場合は全てのループを対象とするため、-1を全てのループの意味で使用する
        public static StepUpGachaLoopCountTarget All { get; } = new(-1);

        public bool IsEmpty() => ReferenceEquals(this, Empty);
        
        public bool IsAll() => ReferenceEquals(this, All);
    }
}

