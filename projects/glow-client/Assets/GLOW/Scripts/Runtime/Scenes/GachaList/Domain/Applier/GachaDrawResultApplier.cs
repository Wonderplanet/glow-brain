using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Tutorial;
using GLOW.Core.Domain.Repositories;
using Zenject;

namespace GLOW.Scenes.GachaList.Domain.Applier
{
    public class GachaDrawResultApplier : IGachaDrawResultApplier
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IAcquisitionDisplayedUnitIdsRepository AcquisitionDisplayedUnitIdsRepository { get; }

        public void UpdateGachaResult(GachaDrawResultModel resultModel)
        {
            // ガシャ結果の保存
            var preFetchModel = GameRepository.GetGameFetch();
            var preFetchOtherModel = GameRepository.GetGameFetchOther();

            // ユーザーパラメータ・所持アイテムの更新
            var newFetch = preFetchModel with
            {
                UserParameterModel = resultModel.UserParameterModel
            };

            var newFetchOtherModel = preFetchOtherModel with
            {
                UserGachaModels = preFetchOtherModel.UserGachaModels.Update(resultModel.UserGachaModel),
                UserGachaDrawCountThresholdModels = preFetchOtherModel.UserGachaDrawCountThresholdModels.Update(
                    resultModel.UserDrawCountThresholdModels),
                UserUnitModels = preFetchOtherModel.UserUnitModels.Update(resultModel.UserUnitModels),
                UserItemModels = preFetchOtherModel.UserItemModels.Update(resultModel.UserItemModels)
            };
            // 消費物・獲得物のセーブ
            GameManagement.SaveGameUpdateAndFetch(newFetch, newFetchOtherModel);
            
            // ガチャ結果を元に保存
            AcquisitionDisplayedUnitIdsRepository.SetAcquisitionDisplayedUnitIds(
                newFetchOtherModel.UserUnitModels
                    .Select(model => model.MstUnitId)
                    .Distinct()
                    .ToList()
            );
        }
    }
}
