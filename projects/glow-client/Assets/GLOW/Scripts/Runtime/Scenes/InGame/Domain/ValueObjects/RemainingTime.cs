using Cysharp.Text;

namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record RemainingTime(int Milliseconds)
    {
        public override string ToString()
        {
            int seconds = Milliseconds / 1000;
            int minutes = seconds / 60;

            return ZString.Format("{0:00}:{1:00}", minutes, seconds % 60);
        }
    }
}
