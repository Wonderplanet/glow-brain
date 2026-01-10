using System.Globalization;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record InGameScore(ObscuredLong Value)
    {
        public static InGameScore Empty { get; } = new(0);
        public static InGameScore Zero { get; } = new(0);
        static readonly ObscuredLong MaxValue = 999999999999;
        public static InGameScore Max { get; } = new(MaxValue);

        public ObscuredLong Value { get; } = Value >= MaxValue ? MaxValue : Value;

        public static InGameScore operator +(InGameScore a, long b)
        {
            long newValue = a.Value + b;
            return new InGameScore(newValue);
        }

        public static InGameScore operator +(InGameScore a, InGameScore b)
        {
            long newValue = a.Value + b.Value;
            return new InGameScore(newValue);
        }

        public static InGameScore operator +(InGameScore a, Damage b)
        {
            long newValue = a.Value + b.Value;
            return new InGameScore(newValue);
        }

        public static bool operator >(InGameScore a, InGameScore b)
        {
            return a.Value > b.Value;
        }

        public static bool operator <(InGameScore a, InGameScore b)
        {
            return a.Value < b.Value;
        }

        public static bool operator >=(InGameScore a, InGameScore b)
        {
            return a.Value >= b.Value;
        }

        public static bool operator <=(InGameScore a, InGameScore b)
        {
            return a.Value <= b.Value;
        }

        public static InGameScore FromEnhanceQuestScore(EnhanceQuestScore enhanceQuestScore)
        {
            return new InGameScore((long)enhanceQuestScore.Value);
        }

        public long ToLong()
        {
            return Value;
        }

        public override string ToString()
        {
            return Value.ToString("N0", CultureInfo.InvariantCulture);
        }

        public AdventBattleScore ToAdventBattleScore()
        {
            return new AdventBattleScore(Value);
        }

        public bool IsZero()
        {
            return Value == 0;
        }
    }
}
