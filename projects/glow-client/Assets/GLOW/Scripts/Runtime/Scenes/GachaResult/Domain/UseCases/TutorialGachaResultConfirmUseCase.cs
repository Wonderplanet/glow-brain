using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Repositories;
using GLOW.Modules.Tutorial.Domain.Applier;
using GLOW.Modules.Tutorial.Domain.Definitions;
using Zenject;

namespace GLOW.Scenes.GachaResult.Domain.UseCases
{
    public class TutorialGachaResultConfirmUseCase
    {
        [Inject] ITutorialService TutorialService { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IAcquisitionDisplayedUnitIdsRepository AcquisitionDisplayedUnitIdsRepository { get; }
        [Inject] ITutorialGachaConfirmedApplier TutorialGachaConfirmedApplier { get; }

        public async UniTask ConfirmTutorialGachaResult(CancellationToken cancellationToken)
        {
            // API通信 : ガシャ結果の確定を受け取る
            var result = await TutorialService.GachaConfirm(cancellationToken);
            
            // URキャラを取得 なければ最初のキャラを取得
            var unit = TutorialGachaConfirmedApplier.GetUrUnitIdOrFirst(result.UserUnitModels);
            
            // 編成更新とアバター更新のAPI通信
            var (resultParty, resultChangeAvatar) = await TutorialGachaConfirmedApplier.ApplyPartyAndAvatar(
                cancellationToken,
                unit);
            
            // ガシャ結果の保存
            var preFetchModel = GameRepository.GetGameFetch();
            var preFetchOtherModel = GameRepository.GetGameFetchOther();
            
            // ユーザーパラメータ・所持アイテムの更新
            var newFetch = preFetchModel with
            {
                UserParameterModel = result.UserParameterModel
            };
            
            var newFetchOtherModel = preFetchOtherModel with
            {
                TutorialStatus = result.TutorialStatusModel,
                UserUnitModels = preFetchOtherModel.UserUnitModels.Update(result.UserUnitModels),
                UserItemModels = preFetchOtherModel.UserItemModels.Update(result.UserItemModels),
                UserPartyModels = preFetchOtherModel.UserPartyModels.Update(resultParty.Parties),
                UserProfileModel = resultChangeAvatar.UserProfileModel
            };
            
            // 受け取ったユニットIDを保存
            AcquisitionDisplayedUnitIdsRepository.SetAcquisitionDisplayedUnitIds(
                newFetchOtherModel.UserUnitModels
                    .Select(model => model.MstUnitId)
                    .Distinct()
                    .ToList());
            
            // 消費物・獲得物のセーブ
            GameManagement.SaveGameUpdateAndFetch(newFetch, newFetchOtherModel);
        }
    }
}
