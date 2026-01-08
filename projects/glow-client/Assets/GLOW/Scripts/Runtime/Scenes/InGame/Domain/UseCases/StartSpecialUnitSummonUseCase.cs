using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    public class StartSpecialUnitSummonUseCase
    {
        [Inject] IInGameScene InGameScene { get; }
        [Inject] IDeckSpecialUnitSummonEvaluator DeckSpecialUnitSummonEvaluator { get; }

        public StartSpecialUnitSummonUseCaseModel StartSpecialUnitSummon(MasterDataId characterId)
        {
            if (InGameScene.IsBattleOver) return StartSpecialUnitSummonUseCaseModel.Empty;

            DeckUnitModel deckUnit = InGameScene.DeckUnits.FirstOrDefault(
                model => model.CharacterId == characterId,
                DeckUnitModel.Empty);

            var canSummon = DeckSpecialUnitSummonEvaluator.CanSummon(
                deckUnit,
                InGameScene.BattlePointModel,
                InGameScene.SpecialUnitSummonInfoModel,
                InGameScene.SpecialUnits,
                InGameScene.SpecialUnitSummonQueue,
                BattleSide.Player);

            if (!canSummon) return StartSpecialUnitSummonUseCaseModel.Empty;

            var summoningKomaIds = InGameScene.SpecialUnits
                .Where(unit => unit.BattleSide == BattleSide.Player)
                .Select(unit => unit.LocatedKoma.Id)
                .Distinct()
                .ToList();

            // 使用する必殺技がコマ選択か必要か取得
            var needTargetSelectTypeFlag = DeckSpecialUnitSummonEvaluator.NeedsTargetSelection(deckUnit.SpecialAttackData);

            // スペシャルキャラ召喚中にする
            InGameScene.SpecialUnitSummonInfoModel = InGameScene.SpecialUnitSummonInfoModel with
            {
                IsSummonPositionSelecting = SpecialUnitSummonPositionSelectingFlag.True
            };

            return new StartSpecialUnitSummonUseCaseModel(
                InGameScene.SpecialUnitSummonInfoModel.CanSpecialUnitSummonFlag,
                InGameScene.SpecialUnitSummonInfoModel.KomaRange,
                InGameScene.KomaDictionary,
                summoningKomaIds,
                needTargetSelectTypeFlag);
        }
    }
}
