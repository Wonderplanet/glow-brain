namespace GLOW.Core.Domain.Models.Pvp
{
    // 集計中など、サーバーからランキング閲覧状態が大丈夫か見るためのrecord
    public record ViewableRankingFromCalculatingFlag(bool Value)
    {
        public static ViewableRankingFromCalculatingFlag False { get; } = new ViewableRankingFromCalculatingFlag(false);
        public static ViewableRankingFromCalculatingFlag True { get; } = new ViewableRankingFromCalculatingFlag(true);

        public static implicit operator bool(ViewableRankingFromCalculatingFlag flag) => flag.Value;


        public bool IsEmpty()
        {
            return ReferenceEquals(this, False);
        }
    }
}
