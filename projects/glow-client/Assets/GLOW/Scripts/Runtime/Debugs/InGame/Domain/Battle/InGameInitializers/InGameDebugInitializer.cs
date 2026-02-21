#if GLOW_INGAME_DEBUG
using System.Linq;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Debugs.Home.Domain.Constants;
using GLOW.Debugs.InGame.Domain.Definitions;
using GLOW.Debugs.InGame.Domain.ValueObjects;
using GLOW.Debugs.InGame.Domain.Models;
using GLOW.Debugs.InGame.Domain.UseCases;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine;
using Zenject;

namespace GLOW.Debugs.InGame.Domain.Battle.InGameInitializers
{
    public class InGameDebugInitializer : IInGameDebugInitializer
    {
        [Inject] IMstEnemyCharacterDataRepository MstEnemyCharacterDataRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IInGameDebugSettingRepository DebugSettingRepository { get; }

        public InGameDebugModel Initialize(
            IMstInGameModel mstInGameModel,
            MstAutoPlayerSequenceModel enemyAutoPlayerSequenceModel,
            OutpostModel playerOutpost,
            OutpostModel enemyOutpost,
            IReadOnlyList<DeckUnitModel> pvpOpponentDeckUnits,
            InGameType inGameType)
        {
            List<DebugEnemyInfoModel> enemyInfos;
            if (DebugSettingRepository.Get().IsOverrideSummons)
            {
                enemyInfos = DebugMstUnitTemporaryParameterDefinitions.DebugEnemyInfoModels;
            }
            else
            {
                enemyInfos = CreateEnemyInfoModels(
                    mstInGameModel,
                    enemyAutoPlayerSequenceModel,
                    pvpOpponentDeckUnits,
                    inGameType);
            }

            var debugModel = new InGameDebugModel(
                false,
                false,
                false,
                DamageInvalidationFlag.False,
                DamageInvalidationFlag.False,
                playerOutpost.DamageInvalidationFlag,
                enemyOutpost.DamageInvalidationFlag,
                enemyInfos,
                new List<DebugFieldUnitInfoModel>(),
                OutpostEnhancementModel.Empty,
                TickCount.One
            );

            return debugModel;
        }

        List<DebugEnemyInfoModel> CreateEnemyInfoModels(
            IMstInGameModel mstInGameModel,
            MstAutoPlayerSequenceModel enemyAutoPlayerSequenceModel,
            IReadOnlyList<DeckUnitModel> pvpOpponentDeckUnits,
            InGameType inGameType)
        {
            List<DebugEnemyInfoModel> enemyInfos;

            if (inGameType == InGameType.Pvp)
            {
                // PvPモードの場合は対戦相手のデッキユニット情報から召喚リストを生成
                enemyInfos = CreatePvpEnemyInfos(pvpOpponentDeckUnits);
            }
            else
            {
                // 通常モードの場合は従来通りの処理
                var mstAutoPlayerSequenceSetId = mstInGameModel.MstAutoPlayerSequenceSetId;

                enemyInfos = enemyAutoPlayerSequenceModel.Elements
                    .Where(element => element.SequenceSetId == mstAutoPlayerSequenceSetId)
                    .Where(element => element.Action.Type == AutoPlayerSequenceActionType.SummonEnemy)
                    .Select(CreateAutoPlayerSequenceEnemyInfo)
                    .ToList();
            }

            return enemyInfos;
        }

        DebugEnemyInfoModel CreateAutoPlayerSequenceEnemyInfo(MstAutoPlayerSequenceElementModel element)
        {
            var enemyStageParameterId = element.Action.Value.ToMasterDataId();
            var enemyStageParameterModel = MstEnemyCharacterDataRepository.GetEnemyStageParameter(enemyStageParameterId);
            var enemyModel = MstEnemyCharacterDataRepository.GetEnemyCharacter(enemyStageParameterModel.MstEnemyCharacterId);

            return new DebugEnemyInfoModel(
                new DebugSummonTargetId(element.SequenceElementId.Value),
                enemyModel.Name,
                enemyStageParameterModel.Kind
            );
        }

        List<DebugEnemyInfoModel> CreatePvpEnemyInfos(IReadOnlyList<DeckUnitModel> pvpOpponentDeckUnits)
        {
            var enemyInfos = new List<DebugEnemyInfoModel>();

            // 対戦相手のデッキユニット（通常ユニットとスペシャルユニット両方）を取得
            for (var i = 0; i < pvpOpponentDeckUnits.Count; i++)
            {
                var unit = pvpOpponentDeckUnits[i];
                var mstCharacter = MstCharacterDataRepository.GetCharacter(unit.CharacterId);

                enemyInfos.Add(new DebugEnemyInfoModel(
                    new DebugSummonTargetId(i.ToString()),
                    mstCharacter.Name,
                    CharacterUnitKind.Normal  // PvPではすべて通常扱い
                ));
            }

            return enemyInfos;
        }
    }
}
#endif

