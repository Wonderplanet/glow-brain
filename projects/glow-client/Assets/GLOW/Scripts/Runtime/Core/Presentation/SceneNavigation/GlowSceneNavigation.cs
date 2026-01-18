using System.Threading;
using Cysharp.Threading.Tasks;
using UnityEngine;
using WonderPlanet.SceneManagement;
using Zenject;

namespace GLOW.Core.Presentation.SceneNavigation
{
    public class GlowSceneNavigation : IGlowSceneNavigation
    {
        [Inject] ITransitionFactory TransitionFactory { get; }

        ISceneTransition _transition;

        public async UniTask SwitchViaIntermediateScene<T>(
            CancellationToken cancellationToken,
            string intermediateSceneName,
            string destinationSceneName)
            where T : ISceneTransition
        {
            if (_transition != null)
            {
                return;
            }

            _transition = TransitionFactory.CreateTransition<T>(typeof(T).Name);

            var sourceScene = GetCurrentScene();
            var intermediateScene = new SceneHandler(intermediateSceneName);
            var destinationScene = new SceneHandler(destinationSceneName);
            var operation = new SwitchViaIntermediateOperation(sourceScene, intermediateScene, destinationScene);
            _transition.Operation = operation;

            await _transition.Play(cancellationToken);

            _transition = null;
        }

        SceneHandler GetCurrentScene()
        {
            // ISceneNavigationからシーンを取得できないため、Unityのシーンマネージャーから取得
            var activeScene = UnityEngine.SceneManagement.SceneManager.GetActiveScene();
            return new SceneHandler(activeScene);
        }

        class SwitchViaIntermediateOperation : ITransitionOperation
        {
            SceneHandler _sourceScene;
            SceneHandler _intermediateScene;
            SceneHandler _destinationScene;
            bool _isIntermediateSceneLoaded;
            bool _isDestinationSceneLoadStarted;

            public SwitchViaIntermediateOperation(SceneHandler sourceScene, SceneHandler intermediateScene, SceneHandler destinationScene)
            {
                _sourceScene = sourceScene;
                _intermediateScene = intermediateScene;
                _destinationScene = destinationScene;
            }

            public void OnTransitionBegin() { }

            public void SourceSceneWillDisappear()
            {
                if (_sourceScene != null)
                {
                    _sourceScene.BeginAppearanceTransition(false);
                }
            }

            public void SourceSceneDidDisappear()
            {
                if (_sourceScene != null)
                {
                    _sourceScene.EndAppearanceTransition();
                }
                // UnloadSourceAndLoadDestinationAsync()はDestinationPrepare()で既に開始されている
            }

            async UniTask UnloadSourceAndLoadDestinationAsync()
            {
                try
                {
                    // 1. 古いソースシーンをアンロード
                    if (_sourceScene != null)
                    {
                        await _sourceScene.UnLoadScene();
                        await Resources.UnloadUnusedAssets();
                    }

                    // 2. 中間シーンが準備完了するまで待機(タイムアウト付き)
                    var waitCount = 0;
                    const int maxWaitCount = 180; // 約3秒

                    while (!_isIntermediateSceneLoaded)
                    {
                        await UniTask.Yield();
                        waitCount++;

                        if (waitCount >= maxWaitCount)
                        {
                            Debug.LogWarning($"[SwitchViaIntermediateOperation] Timeout waiting for intermediate scene!");
                            break;
                        }
                    }

                    // 3. 少し待機
                    await UniTask.Delay(200);

                    // 4. 中間シーンをアンロード
                    await _intermediateScene.UnLoadScene();

                    // 5. 追加の待機(中間シーンが完全にアンロードされるまで)
                    await UniTask.Delay(200);

                    // 6. 目的のシーンをロード
                    _destinationScene.LoadScene();
                    _isDestinationSceneLoadStarted = true;
                }
                catch (System.Exception ex)
                {
                    Debug.LogError($"[SwitchViaIntermediateOperation] Error: {ex}");
                    _isDestinationSceneLoadStarted = true;
                }
            }

            public void DestinationPrepare()
            {
                // 中間シーンを即座にロード
                _intermediateScene.LoadScene();

                // 中間シーンの準備完了を監視
                WaitForIntermediateSceneAsync().Forget();

                // バックグラウンドで古いシーンのアンロードと新しいシーンのロードを開始
                UnloadSourceAndLoadDestinationAsync()
                    .Forget(ex =>
                    {
                        Debug.LogError($"[SwitchViaIntermediateOperation] UnloadSourceAndLoadDestinationAsync error: {ex}");
                        _isDestinationSceneLoadStarted = true;
                    });
            }

            async UniTask WaitForIntermediateSceneAsync()
            {
                try
                {
                    var waitCount = 0;
                    const int maxWaitCount = 180; // 約3秒

                    while (!_intermediateScene.IsScenePrepared)
                    {
                        await UniTask.Yield();
                        waitCount++;

                        if (waitCount >= maxWaitCount)
                        {
                            Debug.LogWarning(
                                $"[SwitchViaIntermediateOperation] Timeout waiting for intermediate scene preparation!");
                            break;
                        }
                    }

                    _isIntermediateSceneLoaded = true;
                }
                catch (System.Exception ex)
                {
                    Debug.LogError($"[SwitchViaIntermediateOperation] Error waiting for intermediate scene: {ex}");
                    _isIntermediateSceneLoaded = true;
                }
            }

            public void DestinationSceneWillAppear()
            {
                _destinationScene.BeginAppearanceTransition(true);
            }

            public void DestinationSceneDidAppear()
            {
                _destinationScene.EndAppearanceTransition();
            }

            public bool DestinationSceneIsReady()
            {
                // 中間シーン方式では、目的シーンがロード開始されるまでfalseを返す
                // これにより、トランジションは中間シーンで止まらず、目的シーンの準備完了を待つ
                if (!_isDestinationSceneLoadStarted)
                {
                    return false;
                }

                // 目的シーンがロード開始されたら、目的シーンの準備完了を返す
                return _destinationScene.IsScenePrepared;
            }

            public void OnTransitionEnd() { }

            public bool IsSkipSourceMask()
            {
                return _sourceScene == null;
            }
        }
    }
}

