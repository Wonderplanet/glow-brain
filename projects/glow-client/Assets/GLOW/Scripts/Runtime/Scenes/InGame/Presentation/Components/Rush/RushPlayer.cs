using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Domain.Constants;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.InGame.Presentation.Components.Rush
{
    public class RushPlayer : MonoBehaviour
    {
        // 開始時演出
        [SerializeField] StartRushLayer _startRushLayer;
        [SerializeField] StartRushLayer _startRushLayerSmall;
        // 本体演出
        [SerializeField] RushLayer _rushLayer;
        // 終了時演出
        [SerializeField] EndRushLayer _endRushLayer;
        // PVP対戦用の演出
        [SerializeField] PvpOpponentRushLayer _pvpOpponentRushLayer;
        [SerializeField] EndRushLayer _pvpOpponentEndRushLayer;
        // スキップボタン
        [SerializeField] Button _skipButton;

        CancellationTokenSource _animationCancellationTokenSource;
        readonly MultipleSwitchController _pauseController = new ();

        bool _isPlaying;
        bool _isSkipEnabled;
        Action _unpauseAction;

        void Awake()
        {
            _pauseController.OnStateChanged = OnPause;

            _startRushLayer.gameObject.SetActive(false);
            _startRushLayerSmall.gameObject.SetActive(false);
            _rushLayer.gameObject.SetActive(false);
            _endRushLayer.gameObject.SetActive(false);
            _pvpOpponentRushLayer.gameObject.SetActive(false);
            _pvpOpponentEndRushLayer.gameObject.SetActive(false);
        }

        void OnDestroy()
        {
            _animationCancellationTokenSource?.Dispose();
            _animationCancellationTokenSource = null;
        }

        public void Initialize(IUnitImageContainer unitImageContainer)
        {
            _rushLayer.Initialize(unitImageContainer);
            _pvpOpponentRushLayer.Initialize(unitImageContainer);
        }

        public MultipleSwitchHandler Pause(MultipleSwitchHandler handler)
        {
            _pauseController.TurnOn(handler);

            _rushLayer.PauseUnitImage(handler);

            return handler;
        }

        void OnPause(bool isPause)
        {
            _startRushLayer.Pause(isPause);
            _startRushLayerSmall.Pause(isPause);
            _rushLayer.Pause(isPause);
            _endRushLayer.Pause(isPause);
            _pvpOpponentRushLayer.Pause(isPause);
            _pvpOpponentEndRushLayer.Pause(isPause);
        }

        public async UniTask Play(
            IReadOnlyList<UnitAssetKey> unitAssetKeys,
            IReadOnlyList<UnitAssetKey> specialUnitAssetKey,
            PercentageM specialUnitBonus,
            Action unpauseAction,
            RushChargeCount chargeCount,
            AttackPower calculatedRushAttackPower,
            RushEvaluationType rushEvaluationType,
            CancellationToken cancellationToken)
        {
            _animationCancellationTokenSource?.Dispose();
            _animationCancellationTokenSource = CancellationTokenSource.CreateLinkedTokenSource(
                cancellationToken,
                this.GetCancellationTokenOnDestroy());

            var linkedCancellationToken = _animationCancellationTokenSource.Token;

            await UniTask.WaitWhile(() => _isPlaying, cancellationToken: linkedCancellationToken);

            _isPlaying = true;
            _unpauseAction = unpauseAction;

            try
            {
                CleanUp();

                var currentStartRushLayer = unitAssetKeys.Count > 5 ? _startRushLayer : _startRushLayerSmall;
                
                currentStartRushLayer.SetupRushLevel(chargeCount);
                currentStartRushLayer.SetupRushUnitImage(unitAssetKeys);
                
                _rushLayer.SetUpUnitImage(unitAssetKeys);
                
                _endRushLayer.SetUpEndRushResultLayer(chargeCount, calculatedRushAttackPower, rushEvaluationType);
                _rushLayer.SetUpEndRushResultLayer(chargeCount, calculatedRushAttackPower, rushEvaluationType);

                // スペシャルキャラが存在する場合
                if (specialUnitAssetKey.Count > 0)
                {
                    _rushLayer.SetSpecialUnitCutInHidden(false);
                    var specialUnitBonusStr = ZString.Format("{0}%", specialUnitBonus.ToStringF2());
                    _rushLayer.SetSpecialUnitBonusText(specialUnitBonusStr);
                    _rushLayer.SetUpSpecialUnitAsset(specialUnitAssetKey);
                }

                _rushLayer.OnEndTimelineSignalAction = () =>
                {
                    _isSkipEnabled = false;
                    _skipButton.gameObject.SetActive(false);
                };

                _rushLayer.OnUnitAttackAnimationStartSignalAction = () =>
                {
                    _rushLayer.PlayUnitAttackAnimation();
                };

                _rushLayer.OnUnpauseSignalAction = () =>
                {
                    _unpauseAction?.Invoke();
                    _unpauseAction = null;
                };

                _endRushLayer.OnUnpauseSignalAction = () =>
                {
                    _unpauseAction?.Invoke();
                    _unpauseAction = null;
                };

                _isSkipEnabled = true;
                _skipButton.gameObject.SetActive(true);
                SetSkipButtonAction();

                var rushAnimationCanceled = await PlayRushAnimation(currentStartRushLayer, linkedCancellationToken)
                    .SuppressCancellationThrow();

                cancellationToken.ThrowIfCancellationRequested();
                this.GetCancellationTokenOnDestroy().ThrowIfCancellationRequested();

                if (rushAnimationCanceled)
                {
                    _isSkipEnabled = false;
                    _skipButton.gameObject.SetActive(false);

                    _startRushLayer.Skip();
                    _startRushLayerSmall.Skip();
                    _rushLayer.Skip();
                    _startRushLayer.gameObject.SetActive(false);
                    _startRushLayerSmall.gameObject.SetActive(false);
                    _rushLayer.gameObject.SetActive(false);

                    using var linkedCancellationTokenSourceForEndRush = CancellationTokenSource.CreateLinkedTokenSource(
                        cancellationToken,
                        this.GetCancellationTokenOnDestroy());

                    await _endRushLayer.PlayAsync(linkedCancellationTokenSourceForEndRush.Token);

                    CleanUp();
                }

                CleanUp();
            }
            catch (OperationCanceledException)
            {
                _unpauseAction?.Invoke();
                _unpauseAction = null;

                CleanUp();
                throw;
            }
            finally
            {
                _isSkipEnabled = false;
                if (_skipButton != null)
                {
                    _skipButton.gameObject.SetActive(false);
                }

                _animationCancellationTokenSource?.Dispose();
                _animationCancellationTokenSource = null;

                _isPlaying = false;
            }
        }

        public async UniTask PlayPvpOpponentRush(
            IReadOnlyList<UnitAssetKey> unitAssetKeys,
            Action unpauseAction,
            RushChargeCount chargeCount,
            AttackPower calculatedRushAttackPower,
            CancellationToken cancellationToken)
        {
            _animationCancellationTokenSource?.Dispose();
            _animationCancellationTokenSource = CancellationTokenSource.CreateLinkedTokenSource(
                cancellationToken,
                this.GetCancellationTokenOnDestroy());

            var linkedCancellationToken = _animationCancellationTokenSource.Token;

            await UniTask.WaitWhile(() => _isPlaying, cancellationToken: linkedCancellationToken);

            _isPlaying = true;
            _unpauseAction = unpauseAction;

            try
            {
                CleanUp();
                
                _pvpOpponentRushLayer.SetUpRushLevel(chargeCount);
                _pvpOpponentRushLayer.SetUpUnitImage(unitAssetKeys);
                
                // 敵用の終了演出のダメージ表示セットアップ
                _pvpOpponentEndRushLayer.SetUpEndOpponentRushResultLayer(calculatedRushAttackPower);
                _pvpOpponentRushLayer.SetUpEndOpponentRushResultLayer(calculatedRushAttackPower);

                _pvpOpponentRushLayer.OnEndTimelineSignalAction = () =>
                {
                    _isSkipEnabled = false;
                    _skipButton.gameObject.SetActive(false);
                };

                _pvpOpponentRushLayer.OnUnitAttackAnimationStartSignalAction = () =>
                {
                    _pvpOpponentRushLayer.PlayUnitAttackAnimation();
                };

                _pvpOpponentRushLayer.OnUnpauseSignalAction = () =>
                {
                    _unpauseAction?.Invoke();
                    _unpauseAction = null;
                };

                _pvpOpponentEndRushLayer.OnUnpauseSignalAction = () =>
                {
                    _unpauseAction?.Invoke();
                    _unpauseAction = null;
                };

                _isSkipEnabled = true;
                _skipButton.gameObject.SetActive(true);
                SetSkipButtonAction();

                var rushAnimationCanceled = await PlayPvpOpponentRushAnimation(linkedCancellationToken)
                    .SuppressCancellationThrow();

                cancellationToken.ThrowIfCancellationRequested();
                this.GetCancellationTokenOnDestroy().ThrowIfCancellationRequested();

                if (rushAnimationCanceled)
                {
                    _isSkipEnabled = false;
                    _skipButton.gameObject.SetActive(false);

                    _pvpOpponentRushLayer.Skip();
                    _pvpOpponentRushLayer.gameObject.SetActive(false);

                    using var linkedCancellationTokenSourceForEndRush = CancellationTokenSource.CreateLinkedTokenSource(
                        cancellationToken,
                        this.GetCancellationTokenOnDestroy());

                    await _pvpOpponentEndRushLayer.PlayAsync(linkedCancellationTokenSourceForEndRush.Token);
                }
            }
            catch (OperationCanceledException)
            {
                _unpauseAction?.Invoke();
                _unpauseAction = null;

                throw;
            }
            finally
            {
                if (_skipButton != null)
                {
                    _skipButton.gameObject.SetActive(false);
                }

                _animationCancellationTokenSource?.Dispose();
                _animationCancellationTokenSource = null;

                CleanUp();

                _isPlaying = false;
            }
        }

        async UniTask PlayRushAnimation(StartRushLayer currentStartRushLayer, CancellationToken cancellationToken)
        {
            // 演出の実行
            await currentStartRushLayer.PlayAsync(cancellationToken);
            await _rushLayer.PlayAsync(cancellationToken);
        }

        async UniTask PlayPvpOpponentRushAnimation(CancellationToken cancellationToken)
        {
            // 演出の実行
            await _pvpOpponentRushLayer.PlayAsync(cancellationToken);
        }

        void SetSkipButtonAction()
        {
            _skipButton.onClick.RemoveAllListeners();
            _skipButton.onClick.AddListener(() =>
            {
                // スキップ可能かどうかの判定
                if (_isSkipEnabled)
                {
                    _animationCancellationTokenSource?.Cancel();
                }
            });
        }

        void CleanUp()
        {
            _rushLayer.CleanUp();
            _pvpOpponentRushLayer.CleanUp();

            _startRushLayer.HiddenUnitImage();
            _startRushLayerSmall.HiddenUnitImage();

            _startRushLayer.gameObject.SetActive(false);
            _startRushLayerSmall.gameObject.SetActive(false);
            _rushLayer.gameObject.SetActive(false);
            _endRushLayer.gameObject.SetActive(false);
            _pvpOpponentRushLayer.gameObject.SetActive(false);
            _pvpOpponentEndRushLayer.gameObject.SetActive(false);
        }
    }
}
