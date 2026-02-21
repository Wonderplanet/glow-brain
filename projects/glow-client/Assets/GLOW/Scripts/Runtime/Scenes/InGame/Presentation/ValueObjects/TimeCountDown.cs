using System;

namespace GLOW.Scenes.InGame.Presentation.ValueObjects
{
    /// <summary> 残り時間のカウントダウン表示 </summary>
    public record TimeCountDown(TimeCountDown.EnumTimeCountDownType Time, bool HasBeenDisplayed)
    {
        public static TimeCountDown Empty { get; } = new TimeCountDown(EnumTimeCountDownType.LeftTime10, false);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public enum EnumTimeCountDownType
        {
            LeftTime10,
            LeftTime20,
            LeftTime30
        }

        public float ToSecond()
        {
            switch (Time)
            {
                case EnumTimeCountDownType.LeftTime10:
                    return 10;
                case EnumTimeCountDownType.LeftTime20:
                    return 20;
                case EnumTimeCountDownType.LeftTime30:
                    return 30;
                default:
                    throw new ArgumentOutOfRangeException();
            }
        }
    }
}
