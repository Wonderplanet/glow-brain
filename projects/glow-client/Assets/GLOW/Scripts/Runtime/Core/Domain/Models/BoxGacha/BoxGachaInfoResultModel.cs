namespace GLOW.Core.Domain.Models.BoxGacha
{
    public record BoxGachaInfoResultModel(UserBoxGachaModel UserBoxGachaModel)
    {
        public static BoxGachaInfoResultModel Empty { get; } = new(UserBoxGachaModel.Empty);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}