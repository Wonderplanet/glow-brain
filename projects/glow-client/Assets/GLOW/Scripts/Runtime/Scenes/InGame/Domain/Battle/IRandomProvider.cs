using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface IRandomProvider
    {
        int Range(int min, int max);
        float Range(float min, float max);
        int Range(int max);
        bool Trial(Percentage percentage);
    }
}
