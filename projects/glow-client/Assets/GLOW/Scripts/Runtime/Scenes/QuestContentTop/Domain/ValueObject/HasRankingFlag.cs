namespace GLOW.Scenes.QuestContentTop.Domain.ValueObject
{
    public record HasRankingFlag(bool Value)
    {
        public static HasRankingFlag True => new(true);
        public static HasRankingFlag False => new(false);

        public static implicit operator bool(HasRankingFlag flag) => flag.Value;
    };
}
