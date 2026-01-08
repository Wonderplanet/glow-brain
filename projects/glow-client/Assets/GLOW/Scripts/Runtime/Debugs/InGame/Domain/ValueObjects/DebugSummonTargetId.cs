#if GLOW_INGAME_DEBUG
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Debugs.InGame.Domain.ValueObjects
{
    /// <summary>
    /// デバッグ用敵召喚キャラクターを特定するID
    /// </summary>
    public record DebugSummonTargetId(string Value)
    {
        public static DebugSummonTargetId Empty { get; } = new(string.Empty);

        public AutoPlayerSequenceElementId ToAutoPlayerSequenceElementId()
        {
            return new AutoPlayerSequenceElementId(Value);
        }
        
        public DeckUnitIndex ToDeckUnitIndex()
        {
            return new DeckUnitIndex(int.Parse(Value));
        }

        public override string ToString() => Value;
    }
}
#endif // GLOW_INGAME_DEBUG