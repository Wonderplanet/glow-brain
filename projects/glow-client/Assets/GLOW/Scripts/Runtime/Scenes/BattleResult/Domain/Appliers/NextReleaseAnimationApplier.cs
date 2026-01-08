using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using UnityEngine;
using WonderPlanet.UnityStandard.Extension;
using Zenject;

namespace GLOW.Scenes.BattleResult.Domain.Appliers
{
    public class NextReleaseAnimationApplier : INextReleaseAnimationApplier
    {
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IQuestStageReleaseAnimationRepository QuestStageReleaseAnimationRepository { get; }
        [Inject] IPreferenceRepository PreferenceRepository { get; }

        public void UpdateReleaseAnimationRepository(
            MasterDataId selectedMstStageId,
            IReadOnlyList<StageModel> stageModels,
            IReadOnlyList<UserStageEventModel> userStageEventModels)
        {
            var mstStageModel = MstStageDataRepository.GetMstStage(selectedMstStageId);
            var mstQuestModel = MstQuestDataRepository.GetMstQuestModel(mstStageModel.MstQuestId);

            if (mstQuestModel.QuestType == QuestType.Tutorial)
            {
                // 何もせずに戻る
                return;
            }

            if (mstQuestModel.QuestType == QuestType.Normal)
            {
                UpdateNormal(selectedMstStageId, stageModels);
            }
            else if (mstQuestModel.QuestType == QuestType.Event)
            {
                UpdateEvent(selectedMstStageId, userStageEventModels);
            }
        }

        void UpdateNormal(MasterDataId selectedMstStageId, IReadOnlyList<StageModel> stageModels)
        {
            var selectedStageModel = stageModels.FirstOrDefault(s => s.MstStageId == selectedMstStageId);
            if (selectedStageModel == null || selectedStageModel.ClearCount.Value <= 0)
            {
                var nextReleaseQuest =
                    GetNextReleaseQuest(selectedMstStageId, stageModels.Select(s => s as IStageClearCountable).ToArray());
                var nextReleaseStage =
                    GetNextReleaseStage(selectedMstStageId, stageModels.Select(s => s as IStageClearCountable).ToArray());

                // インストールからSetCurrentHomeTopSelectMstQuestIdを一度も通らない、かつ、ステージ開放演出対象だったとき
                // HomeStageSelectUseCasesで1個前のQuestが設定されてしまう
                // ->prefを更新する必要がある
                if(!nextReleaseQuest.IsEmpty()) PreferenceRepository.SetCurrentHomeTopSelectMstQuestId(nextReleaseQuest);

                if (!nextReleaseQuest.IsEmpty() || !nextReleaseStage.IsEmpty())
                {
                    QuestStageReleaseAnimationRepository.SaveForHomeTop(
                        new ShowReleaseAnimationStatus(nextReleaseQuest, nextReleaseStage)
                    );
                }
            }
        }

        void UpdateEvent(MasterDataId selectedMstStageId, IReadOnlyList<UserStageEventModel> stageEventModels)
        {
            var selectedStageModel = stageEventModels.FirstOrDefault(s => s.MstStageId == selectedMstStageId);
            if (selectedStageModel == null || selectedStageModel.TotalClearCount <= 0)
            {
                var nextReleaseQuest =
                    GetNextReleaseQuest(selectedMstStageId, stageEventModels.Select(s => s as IStageClearCountable).ToArray());
                var nextReleaseStage =
                    GetNextReleaseStage(selectedMstStageId, stageEventModels.Select(s => s as IStageClearCountable).ToArray());

                if (!nextReleaseQuest.IsEmpty()|| !nextReleaseStage.IsEmpty())
                {
                    QuestStageReleaseAnimationRepository.SaveForEventStageSelect(
                        new ShowReleaseAnimationStatus(nextReleaseQuest, nextReleaseStage)
                    );
                }
            }
        }

        MasterDataId GetNextReleaseStage(MasterDataId selectedMstStageId, IStageClearCountable[] countableClearStageModels)
        {
            var newReleaseMstStages = MstStageDataRepository.GetMstStages()
                .Where(m => m.ReleaseRequiredMstStageId == selectedMstStageId).ToList();
            if (!newReleaseMstStages.Any()) return MasterDataId.Empty;

            var haveNotNewReleaseMstStages =
                newReleaseMstStages.Where(m => !countableClearStageModels.Exists(s => s.MstStageId == m.Id));
            //今はステージ開放演出で1種類のステージだけ返している
            return haveNotNewReleaseMstStages.First().Id;
        }

        MasterDataId GetNextReleaseQuest(MasterDataId selectedMstStageId,
            IStageClearCountable[] clearCountableStageModels)
        {
            var newReleaseMstStages = MstStageDataRepository.GetMstStages()
                .Where(m => m.ReleaseRequiredMstStageId == selectedMstStageId).ToList();
            if (!newReleaseMstStages.Any()) return MasterDataId.Empty;

            var haveNotNewReleaseMstStages = newReleaseMstStages
                .Where(m => !clearCountableStageModels.Exists(s => s.MstStageId == m.Id))
                .ToList();

            var selectedMstStage = MstStageDataRepository.GetMstStage(selectedMstStageId);
            //今はクエスト開放演出で1種類のクエストだけ返している

            // 開放されたステージのクエストの情報
            var releasedQuest = haveNotNewReleaseMstStages
                .Select(stage => MstQuestDataRepository.GetMstQuestModel(stage.MstQuestId)).ToList();

            // クリアしたステージのクエストの情報
            var selectedStageQuest = MstQuestDataRepository.GetMstQuestModel(selectedMstStage.MstQuestId);

            // 開放されたステージの情報とクリアしたステージの情報を照らし合わせる
            // 新しく開放されたステージがクリアしたステージと異なっているものが存在しているか？
            // かつそのステージがグループIDが異なるものが存在するか？(別IPが開放されたかどうか)
            if (releasedQuest.Exists(q => q.GroupId != selectedStageQuest.GroupId))
            {
                var mstQuestModel = releasedQuest.First(q => q.GroupId != selectedStageQuest.GroupId);
                return mstQuestModel.Id;
            }
            else if (releasedQuest.Exists(q => q.Id != selectedStageQuest.Id))
            {
                var mstQuestModel = releasedQuest.First(q => q.Id != selectedStageQuest.Id);
                return mstQuestModel.Id;
            }
            else return MasterDataId.Empty;

            // if (haveNotNewReleaseMstStages.Exists(m => m.MstQuestId != selectedMstStage.MstQuestId))
            //     return haveNotNewReleaseMstStages.First(m => m.MstQuestId != selectedMstStage.MstQuestId).MstQuestId;
            // else return MasterDataId.Empty;
        }
    }
}
