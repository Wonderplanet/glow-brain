using System;
using UnityEngine;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    /// <summary>
    /// インゲーム用の制限時間
    /// ValueはTickCountベース
    /// </summary>
    public record InGameTimeLimit(int Value) : IComparable
    {
        public static InGameTimeLimit Empty { get; } = new (0);
        public static InGameTimeLimit HighlightTime { get; } = new (10 * TickCount.TickCountPerSec);
        public static InGameTimeLimit Zero { get; } = new (0);

        public bool IsZero()
        {
            return Value ==  Zero.Value;
        }

        /// <summary> 制限時間を強調表示する時間になっているか </summary>
        public bool IsHighlightTextTime()
        {
            return this <= HighlightTime;
        }

        public static InGameTimeLimit FromTimeLimit(TimeLimit timeLimit)
        {
            return new InGameTimeLimit(timeLimit.Value * TickCount.TickCountPerSec);
        }

        public static bool operator < (InGameTimeLimit a, InGameTimeLimit b)
        {
            return a.Value < b.Value;
        }

        public static bool operator <= (InGameTimeLimit a, InGameTimeLimit b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator > (InGameTimeLimit a, InGameTimeLimit b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >= (InGameTimeLimit a, InGameTimeLimit b)
        {
            return a.Value >= b.Value;
        }

        public static InGameTimeLimit operator - (InGameTimeLimit a, InGameTimeLimit b)
        {
            return new InGameTimeLimit(a.Value - b.Value);
        }

        public static InGameTimeLimit operator - (InGameTimeLimit a, TickCount b)
        {
            return new InGameTimeLimit(a.Value - (int)b.Value);
        }

        public TickCount ToTickCount()
        {
            return TickCount.FromSeconds(ToSeconds());
        }

        public StageClearTime ToStageClearTime()
        {
            return new StageClearTime(TimeSpan.FromSeconds(ToLimitTimeSeconds()));
        }

        public float ToSeconds()
        {
            return Value / (float)TickCount.TickCountPerSec;
        }

        // 仕様上制限時間の0.01秒前が制限時間として扱われるため、0.01秒引いた値を返す
        public float ToLimitTimeSeconds()
        {
            return ToSeconds() - 0.01f;
        }

        public string ToRemainingTimeText()
        {
            return ToSeconds().ToString("F2");
        }

        public int CompareTo(object obj)
        {
            if (obj is InGameTimeLimit other)
            {
                return Value.CompareTo(other.Value);
            }

            return 1;
        }
    }
}
