using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Stage
{
    public record BattleEndConditionValue(ObscuredString Value)
    {
        public static BattleEndConditionValue Empty { get; } = new BattleEndConditionValue(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public DefeatEnemyCount ToDefeatEnemyCount()
        {
            return new DefeatEnemyCount(int.Parse(Value));
        }

        public MasterDataId ToMasterDataId()
        {
            return new MasterDataId(Value);
        }

        public TimeLimit ToTimeLimit()
        {
            return new TimeLimit(int.Parse(Value));
        }
    }
}
