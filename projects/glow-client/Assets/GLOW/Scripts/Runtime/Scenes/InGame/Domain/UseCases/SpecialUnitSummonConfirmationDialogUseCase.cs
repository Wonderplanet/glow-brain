using GLOW.Core.Domain.ModelFactories;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    public class SpecialUnitSummonConfirmationDialogUseCase
    {
        [Inject] IInGameScene InGameScene { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] ISpecialAttackInfoModelFactory SpecialAttackInfoModelFactory { get; }

        public SpecialUnitSummonConfirmationDialogUseCaseModel GetUseCaseModel(
            MasterDataId characterId,
            NeedTargetSelectTypeFlag needTargetSelectTypeFlag)
        {
            var deckUnit = InGameScene.DeckUnits.FirstOrDefault(
                model => model.CharacterId == characterId, 
                DeckUnitModel.Empty);
            
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var userUnit = gameFetchOther.UserUnitModels.Find(unit => unit.UsrUnitId == deckUnit.UserUnitId);
            
            var characterModel = MstCharacterDataRepository.GetCharacter(characterId);
            
            var specialAttackInfoModel = SpecialAttackInfoModelFactory.Create(characterModel, userUnit);

            return new SpecialUnitSummonConfirmationDialogUseCaseModel(
                specialAttackInfoModel.Name,
                specialAttackInfoModel.Description,
                deckUnit.SummonCost,
                needTargetSelectTypeFlag);
        }
    }
}
