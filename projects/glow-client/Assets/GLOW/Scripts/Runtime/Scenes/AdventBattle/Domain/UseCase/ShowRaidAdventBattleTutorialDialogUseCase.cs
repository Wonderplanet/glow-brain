using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Extensions;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Modules.TutorialTipDialog.Domain.Models;
using GLOW.Modules.TutorialTipDialog.Domain.ValueObject;
using GLOW.Scenes.AdventBattle.Domain.Model;
using Zenject;

namespace GLOW.Scenes.AdventBattle.Domain.UseCase
{
    public class ShowRaidAdventBattleTutorialDialogUseCase
    {
        [Inject] IMstTutorialRepository MstTutorialRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        
        public RaidAdventBattleTutorialDialogUseCaseModel GetUseCaseModelIfNeeded()
        {
            // 協力スコア開催外またはチュートリアル完了済みの場合はEmptyを返す
            if (!IsOpeningRaidAdventBattle() || IsCompletedTutorial())
            {
                return RaidAdventBattleTutorialDialogUseCaseModel.Empty;
            }

            var useCaseModel = CreateUseCaseModel();

            return useCaseModel;
        }

        bool IsOpeningRaidAdventBattle()
        {
            var mstAdventBattleModels = MstAdventBattleDataRepository.GetMstAdventBattleModels();

            // 開催時間から対象の降臨バトルのマスターデータを取得
            var mstAdventBattleModel = mstAdventBattleModels.FirstOrDefault(model =>
                CalculateTimeCalculator.IsValidTime(
                    TimeProvider.Now, 
                    model.StartDateTime.Value, 
                    model.EndDateTime.Value), 
                MstAdventBattleModel.Empty);

            // モデルが無い、または降臨バトルが協力スコアでない場合はfalseを返す
            if(mstAdventBattleModel.IsEmpty() || mstAdventBattleModel.BattleType != AdventBattleType.Raid) return false;
            
            return true;
        }

        bool IsCompletedTutorial()
        {
            var freeParts = GameRepository.GetGameFetchOther().UserTutorialFreePartModels;
            
            var isRaidAdventBattleTutorialCompleted = freeParts.Any(
                model => model.TutorialFunctionName == TutorialFreePartIdDefinitions.TransitRaidAdventBattle);
            
            // 協力スコアチュートリアル完了済みの場合はtrueを返す
            if (isRaidAdventBattleTutorialCompleted) return true;
            
            return false;
        }
        
        RaidAdventBattleTutorialDialogUseCaseModel CreateUseCaseModel()
        {
            // tipModelを取得
            var tipModels = CreateTipModels();

            // チュートリアルのモデルが空の場合はEmptyを返す
            if (tipModels.IsEmpty())
            {
                return RaidAdventBattleTutorialDialogUseCaseModel.Empty;
            }

            return new RaidAdventBattleTutorialDialogUseCaseModel(tipModels);
        }

        IReadOnlyList<TutorialTipModel> CreateTipModels()
        {
            var id = TutorialFreePartIdDefinitions.TransitRaidAdventBattle.ToMasterDataId();
            var mstModels = MstTutorialRepository.GetMstTutorialTipModels(id);

            if (mstModels.IsEmpty()) return new List<TutorialTipModel>();
            
            var tipModels = new List<TutorialTipModel>();
            
            for (var i = 0; i < mstModels.Count; i++)
            {
                var model = mstModels[i];

                // 末尾の場合は次へにしない
                var flag = i == mstModels.Count - 1
                    ? ShouldShowNextButtonTextFlag.False
                    : ShouldShowNextButtonTextFlag.True;

                var tutorialTipModel = new TutorialTipModel(
                    model.TutorialTipDialogTitle,
                    TutorialTipAssetPath.FromAssetKey(model.TutorialTipAssetKey),
                    flag
                );

                tipModels.Add(tutorialTipModel);
            }
            
            return tipModels;
        }
    }
}