using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Title.Domains.Definition.Service;
using UnityEngine;
using WonderPlanet.StorageSupporter;
using Zenject;

namespace GLOW.Scenes.TitleResultLinkBnIdDialog.Domain.UseCases
{
    public class LinkBnIdForTitleUseCase
    {
        [Inject] IUserService UserService { get; }
        [Inject] IOverrideAuthenticateTokenService OverrideAuthenticateTokenService { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IPreferenceRepository PreferenceRepository { get; }

        public async UniTask LinkBnId(CancellationToken cancellationToken, BnIdCode code)
        {
            var result = await UserService.LinkBnId(cancellationToken, code, false);
            
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var newGameFetchOther = gameFetchOther with
            {
                BnIdLinkedAt = result.BnIdLinkedAt
            };

            GameManagement.SaveGameFetchOther(newGameFetchOther);

            // Tokenが空の場合は上書きしない
            if (!result.BnIdToken.IsEmpty())
            {
                // 端末保存情報の消去 (Player prefsの削除)
                PreferenceRepository.DeleteAll();
            
                // temporaryCacheの消去 (マスターデータ類の削除)
                DirectorySupport.DeleteFilesAndDirectories(Application.temporaryCachePath, false);

                await OverrideAuthenticateTokenService.OverrideAuthenticateToken(cancellationToken, result.BnIdToken.Value);
            }
        }
    }
}
