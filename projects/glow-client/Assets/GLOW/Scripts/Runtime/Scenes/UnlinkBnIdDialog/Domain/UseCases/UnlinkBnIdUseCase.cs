using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Scenes.Title.Domains.Definition.Service;
using UnityEngine;
using WonderPlanet.StorageSupporter;
using WPFramework.Domain.Services;
using Zenject;

namespace GLOW.Scenes.UnlinkBnIdDialog.Domain.UseCases
{
    public class UnlinkBnIdUseCase
    {
        [Inject] IUserService UserService { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IAuthenticateService AuthenticateService { get; }
        [Inject] IPreferenceRepository PreferencesRepository { get; }

        public async UniTask UnlinkBnId(CancellationToken cancellationToken)
        {
            await UserService.UnlinkBnId(cancellationToken);

            await AuthenticateService.DeleteAuthenticationData(cancellationToken);

            var gameFetchOther = GameRepository.GetGameFetchOther();
            var newGameFetchOther = gameFetchOther with
            {
                BnIdLinkedAt = null,
            };
            GameManagement.SaveGameFetchOther(newGameFetchOther);

            // 端末保存情報の消去 (Player prefsの削除)
            PreferencesRepository.DeleteAll();

            // temporaryCacheの消去 (マスターデータ類の削除)
            DirectorySupport.DeleteFilesAndDirectories(Application.temporaryCachePath, false);
        }
    }
}
