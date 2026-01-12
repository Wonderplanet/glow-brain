using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.EncyclopediaUnitSpecialAttack.Domain.Models;
using GLOW.Scenes.InGame.Domain.Battle.CharacterUnitAction;
using Zenject;

namespace GLOW.Scenes.EncyclopediaUnitSpecialAttack.Domain.UseCases
{
    public class GetUnitSpecialAttackPreviewUseCase
    {
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] ISpecialRoleSpecialAttackFactory SpecialRoleSpecialAttackFactory { get; }

        public UnitSpecialAttackPreviewModel GetUnitSpecialAttack(MasterDataId mstUnitId)
        {
            var mstUnit = MstCharacterDataRepository.GetCharacter(mstUnitId);
            
            var specialAttackData = SpecialRoleSpecialAttackFactory.CreateSpecialRoleSpecialAttack(
                mstUnit,
                UnitGrade.Minimum,
                UnitLevel.One);
            
            return new UnitSpecialAttackPreviewModel(
                mstUnit.Color,
                UnitImageAssetPath.FromAssetKey(mstUnit.AssetKey),
                mstUnit.AssetKey,
                CharacterUnitAttackChargeAction.InitialChargeTime,
                specialAttackData.BaseData.ActionDuration,
                mstUnit.IsEncyclopediaSpecialAttackPositionRight);
        }
    }
}
