namespace GLOW.Modules.InAppReview.Domain.ValueObject
{
    public record InAppReviewFlag(bool Value)
    {
        public static InAppReviewFlag True { get; } = new (true);
        public static InAppReviewFlag False { get; } = new (false);
        
        public static implicit operator bool(InAppReviewFlag flag) => flag.Value;
    }
}