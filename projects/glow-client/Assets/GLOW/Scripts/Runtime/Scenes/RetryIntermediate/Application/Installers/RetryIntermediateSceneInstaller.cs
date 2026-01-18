using Zenject;

namespace GLOW.Scenes.RetryIntermediate.Application.Installers
{
    public class RetryIntermediateSceneInstaller : MonoInstaller
    {
        public override void InstallBindings()
        {
            // 中間シーンは何もバインドしない
            // 必要最小限のシーンとして機能
        }
    }
}

