using UnityEngine;
using WonderPlanet.SceneManagement;

namespace GLOW.Scenes.RetryIntermediate.Application.Delegates
{
    /// <summary>
    /// リトライ時の中間シーン
    /// シーン遷移の緩衝材として機能し、特別な初期化処理は行わない
    /// AbstractSceneDelegateを継承せず、ISceneNavigationDelegateのみを実装することで
    /// Bootstrapの完了を待たずに即座に初期化を完了する
    /// </summary>
    public class RetryIntermediateSceneDelegate : MonoBehaviour, ISceneNavigationDelegate
    {
        public void SceneWillAppear()
        {
        }

        public void SceneDidAppear()
        {
        }

        public void SceneWillDisappear()
        {
        }

        public void SceneDidDisappear()
        {
        }

        public bool IsScenePrepared => true;
    }
}


