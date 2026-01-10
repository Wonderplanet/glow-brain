namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    /// <summary>
    /// 状態変化の付与元を識別するID
    /// </summary>
    /// <param name="Value"></param>
    public record StateEffectSourceId(int Value)
    {
        public static StateEffectSourceId Empty { get; } = new(0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
