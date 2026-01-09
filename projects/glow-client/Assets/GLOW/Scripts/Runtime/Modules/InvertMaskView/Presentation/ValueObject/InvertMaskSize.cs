namespace GLOW.Modules.InvertMaskView.Presentation.ValueObject
{
    public record InvertMaskSize(float Width, float Height)
    {
        public static InvertMaskSize Zero { get; } = new InvertMaskSize(0, 0);
    }
}
