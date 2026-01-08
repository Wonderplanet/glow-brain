namespace GLOW.Modules.CommonReceiveView.Presentation.ValueObject
{
    public record ReceivedRewardDescription(string Value)
    {
        public static ReceivedRewardDescription Empty { get; } = new ReceivedRewardDescription("");
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}