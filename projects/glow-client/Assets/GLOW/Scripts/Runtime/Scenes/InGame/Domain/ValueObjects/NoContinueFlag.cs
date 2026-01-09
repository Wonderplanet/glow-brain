namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    // コンティニュー不可かどうかのフラグ
    public record NoContinueFlag(bool Value)
    {
        public static NoContinueFlag Empty { get; } = new (false);
        public static implicit operator bool(NoContinueFlag flag) => flag.Value;
    }
}