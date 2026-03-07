using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Repositories;
using UnityEngine;
using WonderPlanet.StorageSupporter;
using WPFramework.Domain.Services;
using Zenject;

namespace GLOW.Scenes.TitleMenu.Domain
{
    public class UserDataDeleteUseCase
    {
        [Inject] IAuthenticateService AuthenticateService { get; }
        [Inject] IPreferenceRepository PreferenceRepository { get; }
        
        public async UniTask DeleteUserData(CancellationToken cancellationToken)
        {
            // 端末保存情報の消去 (Player prefsの削除)
            PreferenceRepository.DeleteAll();
            
            // temporaryCacheの消去 (マスターデータ類の削除)
            DirectorySupport.DeleteFilesAndDirectories(Application.temporaryCachePath, false);
            
            // persistentDataの中にあるユーザーログイン情報の消去 (アセットは残すため、persistentData全体は消さない)
            await AuthenticateService.DeleteAuthenticationData(cancellationToken);
        }
    }
}