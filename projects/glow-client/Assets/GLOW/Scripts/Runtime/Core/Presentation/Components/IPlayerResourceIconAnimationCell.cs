namespace GLOW.Core.Presentation.Components
{
    public interface IPlayerResourceIconAnimationCell
    {
        bool Hidden { get; set; }
        void PlayAppearanceAnimation(float normalizedTime = 0.0f);
    }
}
