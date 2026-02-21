using System;
using System.Globalization;
using Cysharp.Text;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Stage
{
    // 強化クエストでのスコア閾値
    public record EnhanceQuestMinThresholdScore(ObscuredInt Value) : IComparable
    {
        public static EnhanceQuestMinThresholdScore Empty { get; } = new EnhanceQuestMinThresholdScore(0);

        public static bool operator > (EnhanceQuestMinThresholdScore a, InGameScore b)
        {
            return a.Value > b.Value;
        }

        public static bool operator < (EnhanceQuestMinThresholdScore a, InGameScore b)
        {
            return a.Value < b.Value;
        }

        public static bool operator >= (EnhanceQuestMinThresholdScore a, InGameScore b)
        {
            return a.Value >= b.Value;
        }

        public static bool operator <= (EnhanceQuestMinThresholdScore a, InGameScore b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator > (EnhanceQuestMinThresholdScore a, EnhanceQuestScore b)
        {
            return a.Value > b.Value;
        }

        public static bool operator < (EnhanceQuestMinThresholdScore a, EnhanceQuestScore b)
        {
            return a.Value < b.Value;
        }

        public static bool operator >= (EnhanceQuestMinThresholdScore a, EnhanceQuestScore b)
        {
            return a.Value >= b.Value;
        }

        public static bool operator <= (EnhanceQuestMinThresholdScore a, EnhanceQuestScore b)
        {
            return a.Value <= b.Value;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public override string ToString()
        {
            return Value.ToString("N0", CultureInfo.InvariantCulture);
        }
        
        public string ToDisplayString()
        {
            return ZString.Format("{0} pt", Value.ToString("N0", CultureInfo.InvariantCulture));
        }

        public int CompareTo(object obj)
        {
            if (obj is EnhanceQuestMinThresholdScore other)
            {
                return Value.CompareTo(other.Value);
            }

            return 1;
        }
    }
}
