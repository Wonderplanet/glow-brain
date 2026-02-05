using System;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants.AdventBattle;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Extensions;
using GLOW.Scenes.AdventBattle.Domain.Definition.Service;
using GLOW.Scenes.AdventBattleRankingResult.Domain.ModelFactories;
using GLOW.Scenes.AdventBattleRankingResult.Domain.Models;
using Zenject;
namespace GLOW.Scenes.AdventBattleRankingResult.Domain.UseCases
{
    public class AdventBattleRankingResultUseCase
    {
        [Inject] IAdventBattleService AdventBattleService { get; }
        [Inject] IAdventBattleRankingResultModelFactory AdventBattleRankingResultModelFactory { get; }
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] IPreferenceRepository PreferenceRepository { get; }

        public async UniTask<AdventBattleRankingResultModel> GetAdventBattleRankingResult(CancellationToken cancellationToken)
        {
            var configModel = MstConfigRepository.GetConfig(MstConfigKey.AdventBattleRankingAggregateHours);
            var aggregateHours = configModel.IsEmpty() 
                ? AdventBattleConst.DefaultAdventBattleRankingAggregateHours
                : configModel.Value.ToInt();
            
            // 降臨バトルのランキング集計後に表示したいため、集計時間を引いた時間を取得
            var dateTimeConsideredAggregateHours = TimeProvider.Now.AddHours(-aggregateHours);
            
            // 最後に集計が終わった降臨バトルのマスターデータを取得
            // NOTE: 値オブジェクトでMaxByBelowUpperLimitを使うと上手くいかないが、DateTimeOffset型だと想定した動作になるので一旦対応。
            // NOTE: 値オブジェクトでMaxByBelowUpperLimitを使うと上手くいかない理由は別途確認
            var lastHeldAdventBattleModel = MstAdventBattleDataRepository.GetMstAdventBattleModels()
                .MaxByBelowUpperLimit(model => (DateTimeOffset)model.EndDateTime.Value, dateTimeConsideredAggregateHours)
                ?? MstAdventBattleModel.Empty;
            
            // 集計が終わった降臨バトルが存在しない場合は空のモデルを返す
            if(lastHeldAdventBattleModel.IsEmpty())
            {
                return AdventBattleRankingResultModel.Empty;
            }
            
            // 既に再生済みのランキング結果のアニメーションを再生しないようにする
            var playedAdventBattleModel = PreferenceRepository.AdventBattleRankingResultAnimationPlayedId;
            if (playedAdventBattleModel == lastHeldAdventBattleModel.Id)
            {
                // 既に再生済みだった場合は再生しない
                return AdventBattleRankingResultModel.Empty;
            }
            
            // 30日以上前に集計が終わった降臨バトルのランキング結果は表示しない
            var daysAdventBattleLastHeld = (dateTimeConsideredAggregateHours - lastHeldAdventBattleModel.EndDateTime).Days;
            if(daysAdventBattleLastHeld > AdventBattleConst.RankingResultNotificationTermDays)
            {
                return AdventBattleRankingResultModel.Empty;
            }
            
            var result = await AdventBattleService.GetInfo(cancellationToken);
            
            // ランキング結果のアニメーションを再生したことを記録
            PreferenceRepository.SetAdventBattleRankingResultAnimationPlayedId(lastHeldAdventBattleModel.Id);
            
            if (result.IsEmpty())
            {
                return AdventBattleRankingResultModel.Empty;
            }
            
            return AdventBattleRankingResultModelFactory.CreateAdventBattleRankingResultModel(result);
        }
    }
}