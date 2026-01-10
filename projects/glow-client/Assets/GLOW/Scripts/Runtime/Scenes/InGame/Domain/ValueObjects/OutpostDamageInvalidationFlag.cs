namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    /// <summary> ゲートダメージ無効化フラグ。クエストタイプによりゲートがダメージで壊れるか分かれる </summary>
    public record OutpostDamageInvalidationFlag(bool Value)
    {
        public static OutpostDamageInvalidationFlag True { get; } = new (true);
        public static OutpostDamageInvalidationFlag False { get; } = new (false);
        public static implicit operator bool(OutpostDamageInvalidationFlag flag) => flag.Value;

        public bool IsDamageInvalidation()
        {
            return Value;
        }
    }
}
