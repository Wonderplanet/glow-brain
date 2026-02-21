using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    /// <summary> AutoPlayerSequenceにて判定値とするコマの移動量 </summary>
    public record PassedKomaCount(int Value)
    {
        public static bool operator >=(KomaCount a, PassedKomaCount b)
        {
            return a.Value >= b.Value;
        }

        public static bool operator <=(KomaCount a, PassedKomaCount b)
        {
            return a.Value <= b.Value;
        }
    }
}
