namespace GLOW.Modules.CommonReceiveView.Presentation.ValueObject
{
    public record RewardTitle(string Value)
    {
        public static RewardTitle Empty { get; } = new RewardTitle("");
        
        public static RewardTitle Default { get; } = new RewardTitle("報酬");

        public bool IsDefault()
        {
            return ReferenceEquals(this, Default);
        }
    }
}