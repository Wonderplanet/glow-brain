namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    // インゲームのステージにてハイスコアを獲得したかのフラグ
    public record NewRecordFlag(bool Value)
    {
        public static NewRecordFlag Empty { get; } = new (false);
        
        public static NewRecordFlag True { get; } = new (true);
        public static NewRecordFlag False { get; } = new (false);
        
        public static implicit operator bool(NewRecordFlag flag) => flag.Value;
    }
}
