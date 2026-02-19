namespace GLOW.Modules.InvertMaskView.Presentation.ValueObject
{
    public record InvertMaskPosition(float X, float Y)
    {
        public static InvertMaskPosition Zero { get; } = new InvertMaskPosition(0, 0);
    }
}
