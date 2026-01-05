using GLOW.Core.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class DefenseTargetFactory : IDefenseTargetFactory
    {
        [Inject] IFieldObjectIdProvider FieldObjectIdProvider { get; }
        [Inject] ICoordinateConverter CoordinateConverter { get; }

        public DefenseTargetModel GenerateDefenseTarget(MstDefenseTargetModel mstDefenseTargetModel)
        {
            var pos = CoordinateConverter.FieldToPlayerOutpostCoord(mstDefenseTargetModel.Position);
            var defenseTargetModel =  new DefenseTargetModel(
                FieldObjectIdProvider.GenerateNewId(),
                mstDefenseTargetModel.AssetKey,
                pos,
                mstDefenseTargetModel.HP,
                mstDefenseTargetModel.HP);
            return defenseTargetModel;
        }
    }
}
