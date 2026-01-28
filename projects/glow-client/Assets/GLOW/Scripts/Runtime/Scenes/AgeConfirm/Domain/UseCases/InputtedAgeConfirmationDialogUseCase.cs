using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.AgeConfirm.Domain
{
    public class InputtedAgeConfirmationDialogUseCase
    {
        [Inject] IShopService ShopService { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IGameRepository GameRepository { get; }

        public async UniTask SetUserAge(CancellationToken cancellationToken, DateOfBirth dateOfBirth)
        {
            // ユーザーの生年月日を設定
            var result = await ShopService.SetUserStoreInfo(cancellationToken, dateOfBirth);

            // ユーザー情報を保存
            var fetchOther = GameRepository.GetGameFetchOther();
            var newFetchOther = fetchOther with { UserStoreInfoModel = result };
            GameManagement.SaveGameFetchOther(newFetchOther);
        }
    }
}
