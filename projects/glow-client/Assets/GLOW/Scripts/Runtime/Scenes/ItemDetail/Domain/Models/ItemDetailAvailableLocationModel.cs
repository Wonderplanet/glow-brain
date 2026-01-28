namespace GLOW.Scenes.ItemDetail.Domain.Models
{
    public record ItemDetailAvailableLocationModel(
        ItemDetailEarnLocationModel EarnLocationModel1,
        ItemDetailEarnLocationModel EarnLocationModel2)
    {
        public static ItemDetailAvailableLocationModel Empty { get; } = new ItemDetailAvailableLocationModel(
            ItemDetailEarnLocationModel.Empty,
            ItemDetailEarnLocationModel.Empty
        );
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}