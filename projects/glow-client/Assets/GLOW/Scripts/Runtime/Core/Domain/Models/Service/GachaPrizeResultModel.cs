namespace GLOW.Core.Domain.Models
{
    public record GachaPrizeResultModel(
        GachaPrizePageModel NormalPrizePageModel,//通常:排出枠
        GachaPrizePageModel NormalPrizeInFixedPageModel,//通常:確定枠
        GachaPrizePageModel UpperPrizeInMaxRarityPageModel,//天井:最高レアリティ枠
        GachaPrizePageModel UpperPrizeInPickupPageModel//天井:ピックアップ枠
    )
    {
        public static GachaPrizeResultModel Empty { get; } = new(
            GachaPrizePageModel.Empty,
            GachaPrizePageModel.Empty,
            GachaPrizePageModel.Empty,
            GachaPrizePageModel.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    };
}
