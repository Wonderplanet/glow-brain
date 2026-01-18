using System.Threading;
using Cysharp.Threading.Tasks;
using WonderPlanet.SceneManagement;

namespace GLOW.Core.Presentation.SceneNavigation
{
    public interface IGlowSceneNavigation
    {
        /// <summary>
        /// 中間シーンを経由してシーンを切り替える。同じシーン名への遷移時に使用。
        /// </summary>
        UniTask SwitchViaIntermediateScene<T>(
            CancellationToken cancellationToken,
            string intermediateSceneName,
            string destinationSceneName)
            where T : ISceneTransition;
    }
}

