namespace GLOW.Core.Domain.Models.BoxGacha
{
    public record BoxGachaResetResultModel(UserBoxGachaModel UserBoxGachaModel)
    {
        public static BoxGachaResetResultModel Empty { get; } = new(UserBoxGachaModel.Empty);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}