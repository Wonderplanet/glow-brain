using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record CommonConditionValue(string Value)
    {
        public static CommonConditionValue Empty { get; } = new CommonConditionValue(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public Percentage ToPercentage()
        {
            return new Percentage(int.Parse(Value));
        }

        public PercentageM ToPercentageM()
        {
            return new PercentageM(int.Parse(Value));
        }

        public TickCount ToTickCount()
        {
            return new TickCount(int.Parse(Value));
        }

        public HP ToHP()
        {
            return new HP(int.Parse(Value));
        }

        public KomaNo ToKomaNo()
        {
            return new KomaNo(int.Parse(Value));
        }

        public AutoPlayerSequenceElementId ToAutoPlayerSequenceElementId()
        {
            return new AutoPlayerSequenceElementId(Value);
        }

        public DefeatEnemyCount ToDefeatEnemyCount()
        {
            return new DefeatEnemyCount(int.Parse(Value));
        }

        public FieldCoordV2 ToFieldCoord()
        {
            return new FieldCoordV2(float.Parse(Value), 0.0f);
        }

        public PassedKomaCount ToPassedKomaCount()
        {
            return new PassedKomaCount(int.Parse(Value));
        }
    }
}
