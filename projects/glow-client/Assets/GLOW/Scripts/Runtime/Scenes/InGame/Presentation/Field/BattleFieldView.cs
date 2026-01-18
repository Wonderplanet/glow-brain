using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Modules.GameOption.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.Battle.CharacterUnitAction;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGame.Presentation.Common;
using GLOW.Scenes.InGame.Presentation.Components;
using GLOW.Scenes.InGame.Presentation.Constants;
using GLOW.Scenes.InGame.Presentation.TimelineTracks;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UnityEngine;
using UnityEngine.AddressableAssets;
using UnityEngine.Experimental.Rendering;
using UnityEngine.Profiling;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    /// <summary>
    /// 戦闘フィールドのクラス。
    ///
    /// 背景やキャラユニットを管理する。
    /// 戦闘フィールドはコマに分割せずに、味方拠点から敵拠点まで一直線のひと続きのフィールドとして扱う。
    /// それをレンダーテクスチャに描画して、
    /// レンダーテクスチャをコマに分割してUIとして表示する（UIとして表示するのはUI側の別クラスの役割）
    /// </summary>
    public class BattleFieldView : MonoBehaviour
    {
        static readonly BattleEffectSignal EffectSignalForBeforeTransformationUnitRemoving = new ("RemoveBeforeUnit");
        static readonly BattleEffectSignal EffectSignalForAfterTransformationUnitSummoning = new ("SummonAfterUnit");
        static readonly float OutpostPosY = 1.0f;

        [SerializeField] Camera _camera;
        [SerializeField] Transform _outpostRoot;
        [SerializeField] Transform _characterUnitRoot;
        [SerializeField] Transform _attackRoot;
        [SerializeField] Transform _defenseTargetRoot;
        [SerializeField] Transform _gimmickObjectRoot;
        [SerializeField] Transform _battleFieldBlackCurtain;
        [SerializeField] Transform _placedItemObjectRoot;
        [SerializeField] AssetReferenceGameObject _playerOutpostPrefabReference;
        [SerializeField] AssetReferenceGameObject _enemyOutpostPrefabReference;
        [SerializeField] AssetReferenceGameObject _characterUnitPrefabReference;
        [SerializeField] AssetReferenceGameObject _specialUnitPrefabReference;
        [SerializeField] AssetReferenceGameObject _defenseTargetViewPrefabReference;
        [SerializeField] AssetReferenceGameObject _gimmickObjectViewPrefabReference;
        [SerializeField] AssetReferenceGameObject _placeItemPrefabReference;

        [Inject] PrefabFactory<OutpostView> OutpostViewFactory { get; }
        [Inject] PrefabFactory<FieldUnitView> CharacterUnitViewFactory { get; }
        [Inject] PrefabFactory<FieldSpecialUnitView> SpecialUnitViewFactory { get; }
        [Inject] PrefabFactory<DefenseTargetView> DefenseTargetViewFactory { get; }
        [Inject] PrefabFactory<InGameGimmickObjectView> GimmickObjectViewFactory { get; }
        [Inject] PrefabFactory<PlacedItemView> PlaceItemViewFactory { get; }
        [Inject] ICoordinateConverter CoordinateConverter { get; }
        [Inject] IViewCoordinateConverter ViewCoordinateConverter { get; }
        [Inject] IUnitImageContainer UnitImageContainer { get; }
        [Inject] BattleEffectManager BattleEffectManager { get; }
        [Inject] BattleSummonEffectManager BattleSummonEffectManager { get; }
        [Inject] BattleScoreEffectManager BattleScoreEffectManager { get; }
        [Inject] BattleStateEffectViewManager BattleStateEffectViewManager { get; }

        OutpostView _playerOutpostPrefab;
        OutpostView _enemyOutpostPrefab;
        FieldUnitView _characterUnitPrefab;
        FieldSpecialUnitView _specialUnitPrefab;
        DefenseTargetView _defenseTargetViewPrefab;
        InGameGimmickObjectView _gimmickObjectViewPrefab;
        PlacedItemView _placedItemViewPrefab;

        IScreenFlashTrackClipDelegate _screenFlashTrackClipDelegate;
        PageComponent _pageComponent;
        Dictionary<KomaId, Rect> _komaUVRectDictionary = new();
        RenderTexture _battleFieldRenderTexture;
        OutpostView _playerOutpostView;
        OutpostView _enemyOutpostView;
        DefenseTargetView _defenseTargetView;
        bool HasDefenseTargetView => _defenseTargetView != null;
        Dictionary<FieldObjectId, FieldUnitView> _unitViewDictionary = new();
        List<FieldSpecialUnitView> _specialUnitViewList = new();
        List<InGameGimmickObjectView> _gimmickObjectViewList = new();
        List<AbstractAttackView> _attackViewList = new();
        Dictionary<FieldObjectId, PlacedItemView> _placedItemViewDictionary = new();
        FieldUnitViewMarchingLaneController _fieldUnitViewMarchingLaneController = new ();

        public RenderTexture BattleFieldRenderTexture => _battleFieldRenderTexture;
        public OutpostView PlayerOutpostView => _playerOutpostView;
        public OutpostView EnemyOutpostView => _enemyOutpostView;
        public FieldViewCoordV2 DefenseTargetPos => HasDefenseTargetView 
            ? _defenseTargetView.FieldViewPos 
            : FieldViewCoordV2.Zero;
        public bool IsDeadAnimation { get; set; } = true;

#if GLOW_DEBUG
        public IReadOnlyList<FieldUnitView> CharacterUnitViewList => _unitViewDictionary.Values.ToList();
#endif

        void Awake()
        {
            _camera.enabled = false;
            HideBlackCurtain();

            SetGlobalZPosition(_outpostRoot, FieldZPositionDefinitions.OutpostRoot);
            SetGlobalZPosition(_defenseTargetRoot, FieldZPositionDefinitions.DefenseTargetRoot);
            SetGlobalZPosition(_gimmickObjectRoot, FieldZPositionDefinitions.GimmickObjectRoot);
            SetGlobalZPosition(_characterUnitRoot, FieldZPositionDefinitions.UnitRoot);
            SetGlobalZPosition(_attackRoot, FieldZPositionDefinitions.AttackRoot);
            SetGlobalZPosition(_battleFieldBlackCurtain, FieldZPositionDefinitions.BlackCurtain);
            SetGlobalZPosition(_placedItemObjectRoot, FieldZPositionDefinitions.PlacedItemObjectRoot);
        }

        void OnDestroy()
        {
            ReleaseLoadedPrefabs();
        }

        public async UniTask InitializeBattleField(
            FieldViewConstructData fieldViewConstructData,
            PageComponent pageComponent,
            IScreenFlashTrackClipDelegate screenFlashTrackClipDelegate,
            CancellationToken cancellationToken)
        {
            ApplicationLog.Log("BattleField", "InitializeBattleField");

            _pageComponent = pageComponent;
            _screenFlashTrackClipDelegate = screenFlashTrackClipDelegate;

            var fieldToFieldViewMatrix = Matrix3x3.Scale(
                -fieldViewConstructData.TierViewWidth, 
                fieldViewConstructData.TierViewWidth);

            ViewCoordinateConverter.SetTransformationMatrix(
                fieldToFieldViewMatrix,
                CoordinateConverter.FieldToPlayerOutpostMatrix,
                CoordinateConverter.FieldToEnemyOutpostMatrix);

            CreateKomaUVRectDictionary(fieldViewConstructData);
            InitializeRenderTexture(fieldViewConstructData);
            SetupBattleFieldOriginPoint(fieldViewConstructData);
            SetupBlackCurtain(fieldViewConstructData);

            await LoadPrefabs(cancellationToken);
        }

        public Rect GetKomaUVRect(KomaId komaId)
        {
            if (_komaUVRectDictionary.TryGetValue(komaId, out Rect uvRect))
            {
                return uvRect;
            }

            return new Rect();
        }

        public void GenerateOutpost(OutpostModel outpostModel)
        {
            var prefab = outpostModel.BattleSide == BattleSide.Player ? _playerOutpostPrefab : _enemyOutpostPrefab;
            var outpost = OutpostViewFactory.Create(prefab);

            outpost.transform.SetParent(_outpostRoot, false);

            outpost.InitializeOutpostView(outpostModel, _pageComponent);

            var pos = ViewCoordinateConverter.ToFieldViewCoord(outpostModel.BattleSide, outpostModel.Pos);
            outpost.transform.localPosition = new Vector3(pos.X, OutpostPosY, 0f);

            if (outpostModel.BattleSide == BattleSide.Player)
            {
                _playerOutpostView = outpost;
            }
            else
            {
                _enemyOutpostView = outpost;
            }
        }

        public FieldUnitView GenerateCharacterUnit(CharacterUnitModel characterUnitModel)
        {
            var fieldUnit = GenerateCharacterUnitWithoutEffect(characterUnitModel);

            var outpostView = characterUnitModel.BattleSide == BattleSide.Player ? _playerOutpostView : _enemyOutpostView;
            outpostView.GenerateSummonEffect();

            return fieldUnit;
        }

        public FieldUnitView GenerateCharacterUnitWithoutEffect(CharacterUnitModel characterUnitModel)
        {
            UnitImage unitImage = InstantiateCharacterImage(characterUnitModel.AssetKey);
            FieldUnitView fieldUnit = CharacterUnitViewFactory.Create(_characterUnitPrefab);

            fieldUnit.transform.SetParent(_characterUnitRoot, false);

            fieldUnit.InitializeCharacterUnitView(
                characterUnitModel,
                unitImage,
                0,
                ViewCoordinateConverter,
                _pageComponent,
                _screenFlashTrackClipDelegate);

            _fieldUnitViewMarchingLaneController.ApplyMarchingPosition(fieldUnit);

            _unitViewDictionary[fieldUnit.Id] = fieldUnit;

            return fieldUnit;
        }

        public FieldSpecialUnitView GenerateSpecialUnit(SpecialUnitModel specialUnitModel)
        {
            UnitImage unitImage = InstantiateCharacterImage(specialUnitModel.AssetKey);
            FieldSpecialUnitView fieldUnit = SpecialUnitViewFactory.Create(_specialUnitPrefab);

            fieldUnit.transform.SetParent(_characterUnitRoot, false);

            fieldUnit.Initialize(
                specialUnitModel,
                unitImage,
                _pageComponent,
                _screenFlashTrackClipDelegate);

            var outpostView = specialUnitModel.BattleSide == BattleSide.Player ? _playerOutpostView : _enemyOutpostView;
            outpostView.GenerateSummonEffect();

            _specialUnitViewList.Add(fieldUnit);

            return fieldUnit;
        }

        public PlacedItemView GeneratePlacedItem(PlacedItemModel placedItemModel)
        {
            var placeItemView = PlaceItemViewFactory.Create(_placedItemViewPrefab);
            placeItemView.transform.SetParent(_placedItemObjectRoot, false);
            placeItemView.InitializePlaceItemView(placedItemModel, ViewCoordinateConverter, BattleStateEffectViewManager);
            SoundEffectPlayer.Play(SoundEffectId.SSE_051_098);
            _placedItemViewDictionary.Add(placeItemView.Id, placeItemView);
            return placeItemView;
        }

        public InGameGimmickObjectView GenerateGimmickObject(InGameGimmickObjectModel gimmickObjectModel)
        {
            var gimmickObjectView = GimmickObjectViewFactory.Create(_gimmickObjectViewPrefab);
            gimmickObjectView.transform.SetParent(_gimmickObjectRoot, false);
            gimmickObjectView.Initialize(gimmickObjectModel, ViewCoordinateConverter);
            _gimmickObjectViewList.Add(gimmickObjectView);
            return gimmickObjectView;
        }

        public DefenseTargetView GenerateDefenseTargetView(DefenseTargetModel defenseTargetModel)
        {
            _defenseTargetView = DefenseTargetViewFactory.Create(_defenseTargetViewPrefab);
            _defenseTargetView.transform.SetParent(_defenseTargetRoot, false);
            _defenseTargetView.Initialize(defenseTargetModel, ViewCoordinateConverter, _pageComponent);
            return _defenseTargetView;
        }

        public void UpdateFieldObjects(
            OutpostModel playerOutpostModel,
            OutpostModel enemyOutpostModel,
            IReadOnlyList<CharacterUnitModel> characterUnitModels,
            IReadOnlyList<SpecialUnitModel> specialUnitModels,
            DefenseTargetModel defenseTarget,
            IReadOnlyList<AppliedAttackResultModel> appliedAttackResults,
            DamageDisplayFlag isDamageDisplay)
        {
            Profiler.BeginSample("UpdateFieldObjects.OutpostUpdate");
            // 拠点の更新
            var playerOutpostPos = ViewCoordinateConverter.ToFieldViewCoord(BattleSide.Player, playerOutpostModel.Pos);
            _playerOutpostView.transform.localPosition = new Vector3(playerOutpostPos.X, 1.0f, 0f);
            _playerOutpostView.UpdateHp(playerOutpostModel.Hp, playerOutpostModel.MaxHp);

            var enemyOutpostPos = ViewCoordinateConverter.ToFieldViewCoord(BattleSide.Enemy, enemyOutpostModel.Pos);
            _enemyOutpostView.transform.localPosition = new Vector3(enemyOutpostPos.X, 1.0f, 0f);
            _enemyOutpostView.UpdateHp(enemyOutpostModel.Hp, enemyOutpostModel.MaxHp);
            Profiler.EndSample();

            Profiler.BeginSample("UpdateFieldObjects.CharacterUnitUpdate");
            // キャラの更新
            foreach (var unitModel in characterUnitModels)
            {
                if (!_unitViewDictionary.TryGetValue(unitModel.Id, out var unitView)) continue;

                // 位置
                unitView.UpdateCharacterUnitView(unitModel, ViewCoordinateConverter);

                // HPゲージの表示非表示(暗闇コマだと非表示)
                unitView.UpdateConditionVisible(unitModel);

                // 効果
                unitView.UpdateStateEffects(unitModel.StateEffects);

                // 被ダメ
                var myAppliedAttackResults = appliedAttackResults
                    .Where(res => res.TargetId == unitView.Id)
                    .ToList();

                if (myAppliedAttackResults.Count != 0)
                {
                    unitView.OnHitAttacks(
                        unitModel, 
                        myAppliedAttackResults, 
                        unitModel.Action.IsDamageInvalidation, 
                        isDamageDisplay);
                    unitView.UpdateHp(unitModel);
                }

                // ヒットストップ
                if (IsHitStop(unitView, appliedAttackResults))
                {
                    unitView.StartHitStop();
                }

                // 撃破された
                if (unitModel.IsDead)
                {
                    OnDeadCharacterUnit(
                        unitView,
                        unitModel.DeathType,
                        unitView.gameObject.GetCancellationTokenOnDestroy()).Forget();
                    continue;
                }

                // 撃破以外での除去
                if (unitModel.IsVanished && !unitModel.Transformation.IsTransformationFinish)
                {
                    RemoveUnitView(unitView);
                    continue;
                }

                // 再出撃
                if (unitModel.IsStateStart(UnitActionState.Restart))
                {
                    unitView.OnRestart();
                }

                // 移動開始。stateがmoveに切り替わったタイミング or 移動停止から再移動に切り替わったタイミング
                if (unitModel.IsStateStart(UnitActionState.Move) && !unitModel.IsMoveStopped ||
                    unitModel.IsStopToMoveStart())
                {
                    unitView.OnStartMove();
                }

                // 待ちモーション
                if (unitModel.IsStateStart(UnitActionState.Engage) ||
                    unitModel.IsStateStart(UnitActionState.PrevMove) ||
                    (unitModel.IsStateStart(UnitActionState.Move) && unitModel.IsMoveStopped) ||
                    unitModel.IsMoveStartToStop())
                {
                    unitView.OnWait();
                }

                // 攻撃準備中エフェクト
                if (unitModel.IsSpecialAttackReady())
                {
                    unitView.ShowSpecialAttackReadyEffect();
                }
                else
                {
                    unitView.HideSpecialAttackReadyEffect();
                }

                 // 攻撃溜め
                if (unitModel.IsStateStart(UnitActionState.AttackCharge))
                {
                    _fieldUnitViewMarchingLaneController.ChangeToSpecialAttackPosition(unitView);
                    unitView.OnStartSpecialAttackCharge();
                }

                // 攻撃
                if (unitModel.IsStateStart(UnitActionState.Attack))
                {
                    unitView.OnAttack(unitModel.NormalAttack);
                }

                // 必殺ワザ
                if (unitModel.IsStateStart(UnitActionState.SpecialAttack))
                {
                    unitView.OnSpecialAttack(unitModel.SpecialAttack);
                }

                // 登場時攻撃
                if (unitModel.IsStateStart(UnitActionState.AppearanceAttack))
                {
                    unitView.OnAppearanceAttack(unitModel.AppearanceAttack);
                }

                // ノックバック
                if (unitModel.IsStateStart(UnitActionState.KnockBack) || unitModel.IsStateStart(UnitActionState.ForceKnockBack))
                {
                    if (unitModel.Action is CharacterUnitKnockBackActionBase knockBackAction)
                    {
                        unitView.OnStartKnockBack(knockBackAction.Duration);
                    }
                }

                // 割り込みスライド
                if (unitModel.IsStateStart(UnitActionState.InterruptSlide))
                {
                    unitView.OnInterruptSlideStarted();
                }

                // 変身
                if (unitModel.IsStateStart(UnitActionState.Transformation))
                {
                    unitView.OnStartTransformationReady();
                }

                // スタン
                if (unitModel.IsStateStart(UnitActionState.Stun))
                {
                    unitView.OnStun();
                }

                // 氷結
                if (unitModel.IsStateStart(UnitActionState.Freeze))
                {
                    unitView.OnFreeze();
                }

                // 必殺ワザ用レーンから戻す
                if (unitModel.Action.ActionState != UnitActionState.SpecialAttack &&
                    unitModel.Action.ActionState != UnitActionState.PreSpecialAttack &&
                    unitModel.Action.ActionState != UnitActionState.AttackCharge)
                {
                    _fieldUnitViewMarchingLaneController.ReturnToMarchingPositionFromSpecialAttackPosition(unitView);
                }
            }
            Profiler.EndSample();

            Profiler.BeginSample("UpdateFieldObjects.SpecialUnitUpdate");
            foreach (var specialUnitModel in specialUnitModels)
            {
                var specialUnitView = _specialUnitViewList.Find(view => view.Id == specialUnitModel.Id);
                if (specialUnitView == null) continue;

                // データ側が必殺技発動タイミングになったのをView側に通知
                if (specialUnitModel.SpecialUnitUseSpecialAttackFlag)
                {
                    specialUnitView.OnSpecialUnitCutInInterruptionStarted();
                }

                // 退去時間になったロールがスペシャルのユニットの削除
                if (specialUnitModel.RemainingLeavingTime.IsEmpty())
                {
                    RemoveSpecialUnitView(specialUnitView);
                }
            }
            Profiler.EndSample();

            Profiler.BeginSample("UpdateFieldObjects.PlayerOutpostDamage");
            // 拠点の被ダメ
            var outpostAttackResults = appliedAttackResults
                .Where(res => res.TargetId == _playerOutpostView.Id)
                .ToList();

            if (outpostAttackResults.Count != 0)
            {
                _playerOutpostView.OnHitAttacks(playerOutpostModel.Hp, outpostAttackResults, isDamageDisplay);
            }
            Profiler.EndSample();

            Profiler.BeginSample("UpdateFieldObjects.EnemyOutpostDamage");
            outpostAttackResults = appliedAttackResults
                .Where(res => res.TargetId == _enemyOutpostView.Id)
                .ToList();

            if (outpostAttackResults.Count != 0)
            {
                _enemyOutpostView.OnHitAttacks(enemyOutpostModel.Hp, outpostAttackResults, isDamageDisplay);
            }
            Profiler.EndSample();

            Profiler.BeginSample("UpdateFieldObjects.DefenseTargetDamage");
            // 防衛オブジェクトの被ダメ
            if (!defenseTarget.IsEmpty() && HasDefenseTargetView)
            {
                _defenseTargetView.SetHp(defenseTarget.Hp);

                var defenseTargetAttackResults = appliedAttackResults
                    .Where(res => res.TargetId == defenseTarget.Id)
                    .ToList();

                if (defenseTargetAttackResults.Count != 0)
                {
                    _defenseTargetView.OnHitAttacks(defenseTarget.Hp, defenseTargetAttackResults);
                }
            }

            Profiler.EndSample();
        }

        public void UpdateAttackViews(IReadOnlyList<IAttackModel> attackModels)
        {
            foreach (var attackModel in attackModels)
            {
                var attackView = _attackViewList.Find(view => view.Id == attackModel.Id);
                if (attackView == null) continue;

                attackView.UpdateAttackView(attackModel);
            }

            var activeAttackViews = new List<AbstractAttackView>();
            foreach (var attackView in _attackViewList)
            {
                if (attackView.IsEnd())
                {
                    Destroy(attackView.gameObject);
                    continue;
                }

                activeAttackViews.Add(attackView);
            }

            _attackViewList = activeAttackViews;
        }

        public void OnEndAttack(IAttackModel attackModel)
        {
            var attackView = _attackViewList.Find(view => view.Id == attackModel.Id);
            if (attackView != null)
            {
                attackView.OnEndAttack(attackModel);
            }
        }

        public void OnEffectBlocked(FieldObjectId fieldObjectId)
        {
            if (_unitViewDictionary.TryGetValue(fieldObjectId, out var unitView))
            {
                unitView.OnEffectBlocked();
            }
        }

        public void OnSurvivedByGuts(FieldObjectId fieldObjectId)
        {
            if (_unitViewDictionary.TryGetValue(fieldObjectId, out var unitView))
            {
                unitView.OnSurvivedByGuts();
            }
        }

        public void OnGimmickObjectsRemoved(IReadOnlyList<InGameGimmickObjectModel> removedGimmickObjectModels)
        {
            // ギミックオブジェクトの削除
            foreach (var model in removedGimmickObjectModels)
            {
                var gimmickView = _gimmickObjectViewList.Find(view => view.Id == model.Id);
                if (gimmickView == null) continue;

                RemoveGimmickObjectView(gimmickView);
            }
        }

        public void OnUpdateScore(IReadOnlyList<ScoreCalculationResultModel> addedScoreModels, Action onCompleted)
        {
            // 加算スコアごとにスコア演出表示
            foreach (var addedScoreModel in addedScoreModels)
            {
                if (_enemyOutpostView.Id == addedScoreModel.SourceFieldObjectId)
                {
                    var outpostEffectView = BattleScoreEffectManager
                        .Generate(_enemyOutpostView.transform.position, addedScoreModel.Score, addedScoreModel.ScoreType)
                        .Play();
                    outpostEffectView.AddCompletedAction(onCompleted);
                    continue;
                }

                if (!_unitViewDictionary.TryGetValue(addedScoreModel.SourceFieldObjectId, out var unitView))
                {
                    continue;
                }

                var effectView = BattleScoreEffectManager
                    .Generate(unitView.EffectRoot.position, addedScoreModel.Score, addedScoreModel.ScoreType)
                    .Play();
                effectView.AddCompletedAction(onCompleted);

                // SE
                var seId = addedScoreModel.ScoreType is InGameScoreType.EnemyDefeat or InGameScoreType.BossEnemyDefeat
                    ? SoundEffectId.SSE_051_054
                    : SoundEffectId.SSE_051_053;

                SoundEffectPlayer.Play(seId);
            }
        }

        public void OnVictory()
        {
            foreach (var characterUnitView in _unitViewDictionary.Values)
            {
                characterUnitView.OnGameEnd();
            }
        }

        public void OnDefeat()
        {
            foreach (var characterUnitView in _unitViewDictionary.Values)
            {
                characterUnitView.OnGameEnd();
            }
        }

        public void OnFinish()
        {
            foreach (var characterUnitView in _unitViewDictionary.Values)
            {
                characterUnitView.OnGameEnd();
            }
        }

        public void PlayerOutpostBroken()
        {
            _playerOutpostView.OnBreakDown();
        }

        public void EnemyOutpostBroken()
        {
            _enemyOutpostView.OnBreakDown();
        }

        public void RecoverPlayerOutpost()
        {
            _playerOutpostView.Recover();
        }

        public async UniTask TransformUnit(
            FieldObjectId beforeUnitId,
            FieldObjectId afterUnitId,
            CancellationToken cancellationToken)
        {
            // 変身前後のUnitViewを取得
            var beforeUnitView = GetCharacterUnitView(beforeUnitId);
            if (beforeUnitView == null) return;

            var afterUnitView = GetCharacterUnitView(afterUnitId);
            if (afterUnitView == null)
            {
                RemoveUnitView(beforeUnitView);
                return;
            }

            // 変身エフェクトを再生
            var effect = BattleEffectManager.Generate(BattleEffectId.UnitTransformation, beforeUnitView.EffectRoot.position);
            if (effect == null)
            {
                RemoveUnitView(beforeUnitView);
                afterUnitView.UnitVisible = true;
                afterUnitView.SetConditionVisible(true);
                return;
            }

            bool isBeforeUnitRemoved = false;
            effect.RegisterSignalAction(EffectSignalForBeforeTransformationUnitRemoving, () =>
            {
                isBeforeUnitRemoved = true;
                RemoveUnitView(beforeUnitView);
            });

            effect.RegisterSignalAction(EffectSignalForAfterTransformationUnitSummoning, () =>
            {
                afterUnitView.UnitVisible = true;
                afterUnitView.SetConditionVisible(true);
            });

            await effect.PlayAsync(cancellationToken);

            // 後始末
            if (!isBeforeUnitRemoved)
            {
                RemoveUnitView(beforeUnitView);
            }

            afterUnitView.UnitVisible = true;
            afterUnitView.SetConditionVisible(true);
        }

        public void SetUnitVisible(FieldObjectId id, bool visible)
        {
            var unitView = GetCharacterUnitView(id);
            if (unitView != null)
            {
                unitView.UnitVisible = visible;
                unitView.SetConditionVisible(visible);
            }
        }

        public MultipleSwitchHandler Pause(MultipleSwitchHandler handler)
        {
            foreach (var characterUnitView in _unitViewDictionary.Values)
            {
                characterUnitView.PauseAnimation(handler);
            }

            foreach (var specialUnitView in _specialUnitViewList)
            {
                specialUnitView.PauseAnimation(handler);
            }

            foreach (var attackView in _attackViewList)
            {
                attackView.Pause(handler);
            }
            
            foreach (var placedItemView in _placedItemViewDictionary.Values)
            {
                placedItemView.Pause(handler);
            }

            BattleEffectManager.PauseAllEffects(handler);
            BattleSummonEffectManager.PauseAllEffects(handler);

            return handler;
        }

        public MultipleSwitchHandler PauseWithoutPlayerUnit(MultipleSwitchHandler handler)
        {
            foreach (var characterUnitView in _unitViewDictionary.Values)
            {
                if (characterUnitView.BattleSide == BattleSide.Player) continue;

                characterUnitView.PauseAnimation(handler);
            }

            BattleEffectManager.PauseAllEffects(handler);
            BattleSummonEffectManager.PauseAllEffects(handler);

            return handler;
        }

        public MultipleSwitchHandler PauseWithout(MultipleSwitchHandler handler, FieldObjectId id)
        {
            foreach (var characterUnitView in _unitViewDictionary.Values)
            {
                if (characterUnitView.Id == id) continue;

                characterUnitView.PauseAnimation(handler);
            }

            foreach (var specialUnitView in _specialUnitViewList)
            {
                if (specialUnitView.Id == id) continue;

                specialUnitView.PauseAnimation(handler);
            }
            
            foreach (var placedItemView in _placedItemViewDictionary)
            {
                if (placedItemView.Key == id) continue;
                
                placedItemView.Value.Pause(handler);
            }

            BattleEffectManager.PauseAllEffects(handler);
            BattleSummonEffectManager.PauseAllEffects(handler);

            return handler;
        }

        public void SetDefenseTargetHighlight(bool isHighlight)
        {
            if (!HasDefenseTargetView) return;
            _defenseTargetView.SetDefenseTargetDisplayHighlight(isHighlight);
        }

        public void SetPlayerOutpostHpHighlight(bool isHighlight)
        {
            _playerOutpostView.SetPlayerOutpostHpHighlight(isHighlight);
        }

        public FieldUnitView GetCharacterUnitView(FieldObjectId id)
        {
            return _unitViewDictionary.TryGetValue(id, out var unitView) ? unitView : null;
        }

        public FieldUnitView GetFirstCharacterUnitViewByCharacterId(MasterDataId characterId, BattleSide battleSide)
        {
            return _unitViewDictionary.Values
                .FirstOrDefault(view => view.CharacterId == characterId && view.BattleSide == battleSide);
        }

        public FieldUnitView FindUnitView(AutoPlayerSequenceElementId autoPlayerSequenceElementId)
        {
            return _unitViewDictionary.Values
                .FirstOrDefault(view => view.AutoPlayerSequenceElementId == autoPlayerSequenceElementId);
        }

        public FieldViewCoordV2 GetCharacterUnitViewPos(FieldObjectId id)
        {
            if (_unitViewDictionary.TryGetValue(id, out var unitView))
            {
                return unitView.FieldViewPos;
            }

            return FieldViewCoordV2.Empty;
        }

        public FieldViewCoordV2 GetCharacterUnitViewTrackingPos(FieldObjectId id)
        {
            if (_unitViewDictionary.TryGetValue(id, out var unitView))
            {
                return unitView.TrackingFieldViewPos;
            }

            return FieldViewCoordV2.Empty;
        }

        public FieldSpecialUnitView GetSpecialUnitView(FieldObjectId id)
        {
            return _specialUnitViewList.Find(view => view.Id == id);
        }

        public InGameGimmickObjectView GetGimmickView(AutoPlayerSequenceElementId autoPlayerSequenceElementId)
        {
            return _gimmickObjectViewList.Find(view => view.AutoPlayerSequenceElementId == autoPlayerSequenceElementId);
        }

        public void ShowBlackCurtain()
        {
            _battleFieldBlackCurtain.gameObject.SetActive(true);
        }

        public void HideBlackCurtain()
        {
            _battleFieldBlackCurtain.gameObject.SetActive(false);
        }

        public void RemoveConsumedItem(PlacedItemModel consumedItemModel)
        {
            _placedItemViewDictionary.TryGetValue(consumedItemModel.PlacedItemId, out var consumedItemView);
            if (consumedItemView == null) return;
            
            _placedItemViewDictionary.Remove(consumedItemModel.PlacedItemId);
            RemoveConsumedItem(consumedItemView).Forget();
        }
        
        public void RemovePlacedItem(PlacedItemModel placedItemModel)
        {
            // 設置アイテムを削除
            _placedItemViewDictionary.TryGetValue(placedItemModel.PlacedItemId, out var placedItemView);
            if (placedItemView == null) return;
            
            _placedItemViewDictionary.Remove(placedItemModel.PlacedItemId);
            Destroy(placedItemView.gameObject);
        }

        async UniTask OnDeadCharacterUnit(
            FieldUnitView fieldUnitView, 
            UnitDeathType deathType, 
            CancellationToken cancellationToken)
        {
            // 撤退するときは他のキャラより表示を手前にする
            if (deathType == UnitDeathType.Escape)
            {
                _fieldUnitViewMarchingLaneController.ChangeToEscapePosition(fieldUnitView);
            }

            await fieldUnitView.OnDead(IsDeadAnimation, deathType, cancellationToken);

            RemoveUnitView(fieldUnitView);
        }

        void RemoveUnitView(FieldUnitView fieldUnitView)
        {
            if (fieldUnitView == null) return;

            _fieldUnitViewMarchingLaneController.ReturnToMarchingPositionFromSpecialAttackPosition(fieldUnitView);
            _unitViewDictionary.Remove(fieldUnitView.Id);
            Destroy(fieldUnitView.gameObject);
        }

        void RemoveSpecialUnitView(FieldSpecialUnitView fieldSpecialUnitView)
        {
            if (fieldSpecialUnitView == null) return;

            _specialUnitViewList.Remove(fieldSpecialUnitView);
            Destroy(fieldSpecialUnitView.gameObject);
        }

        void RemoveGimmickObjectView(InGameGimmickObjectView gimmickObjectView)
        {
            if (gimmickObjectView == null) return;

            _gimmickObjectViewList.Remove(gimmickObjectView);
            Destroy(gimmickObjectView.gameObject);
        }

        void CreateKomaUVRectDictionary(FieldViewConstructData fieldViewConstructData)
        {
            _komaUVRectDictionary.Clear();

            foreach (var pair in fieldViewConstructData.KomaAreaDictionary)
            {
                var areaRect = pair.Value;
                var areaRectMin = new Vector2(areaRect.xMin, areaRect.yMin);
                var areaRectMax = new Vector2(areaRect.xMax, areaRect.yMax);

                var minUv = FieldViewTextureUvCalculator.CalculateUv(fieldViewConstructData, areaRectMin);
                var maxUv = FieldViewTextureUvCalculator.CalculateUv(fieldViewConstructData, areaRectMax);

                var uvRect = new Rect();
                uvRect.xMin = minUv.x;
                uvRect.yMin = minUv.y;
                uvRect.xMax = maxUv.x;
                uvRect.yMax = maxUv.y;

                _komaUVRectDictionary.Add(pair.Key, uvRect);
            }
        }

        void InitializeRenderTexture(FieldViewConstructData fieldViewConstructData)
        {
            ReleaseRenderTexture();

            int width = (int)fieldViewConstructData.FieldViewPixelSize.x;
            int height = (int)fieldViewConstructData.FieldViewPixelSize.y;

            _battleFieldRenderTexture = new RenderTexture(width, height, 24, GraphicsFormat.R8G8B8A8_UNorm);

            _camera.enabled = true;
            _camera.targetTexture = _battleFieldRenderTexture;
            _camera.orthographicSize = fieldViewConstructData.FieldViewRect.height * 0.5f;
        }

        void ReleaseRenderTexture()
        {
            if (_battleFieldRenderTexture != null)
            {
                _camera.targetTexture = null;
                _battleFieldRenderTexture.Release();
            }
        }

        void SetupBattleFieldOriginPoint(FieldViewConstructData fieldViewConstructData)
        {
            float x = fieldViewConstructData.FieldViewOriginPoint.x;
            float y = fieldViewConstructData.FieldViewOriginPoint.y;

            _outpostRoot.localPosition = new Vector3(x, y, _outpostRoot.localPosition.z);
            _characterUnitRoot.localPosition = new Vector3(x, y, _characterUnitRoot.localPosition.z);
            _attackRoot.localPosition = new Vector3(x, y, _attackRoot.localPosition.z);
            _defenseTargetRoot.localPosition = new Vector3(x, y, _defenseTargetRoot.localPosition.z);
            _gimmickObjectRoot.localPosition = new Vector3(x, y, _gimmickObjectRoot.localPosition.z);
            _placedItemObjectRoot.localPosition = new Vector3(x, y, _placedItemObjectRoot.localPosition.z);
        }

        void SetupBlackCurtain(FieldViewConstructData fieldViewConstructData)
        {
            _battleFieldBlackCurtain.localPosition =
                new Vector3(
                    fieldViewConstructData.FieldViewOriginPoint.x + fieldViewConstructData.FieldViewRect.x,
                    fieldViewConstructData.FieldViewOriginPoint.y + OutpostPosY,
                    _battleFieldBlackCurtain.localPosition.z);
            _battleFieldBlackCurtain.localScale =
                new Vector3(
                    fieldViewConstructData.FieldViewRect.width * 2.5f,
                    fieldViewConstructData.FieldViewRect.height * 2.5f,
                    1.0f);
        }

        UnitImage InstantiateCharacterImage(UnitAssetKey assetKey)
        {
            var prefab = UnitImageContainer.Get(UnitImageAssetPath.FromAssetKey(assetKey));
            return Instantiate(prefab).GetComponent<UnitImage>();
        }

        bool IsHitStop(FieldUnitView unit, IReadOnlyList<AppliedAttackResultModel> appliedAttackResults)
        {
            return appliedAttackResults.Any(res => res.AttackerId == unit.Id && res.IsHitStop);
        }

        void SetGlobalZPosition(Transform gameObjectTransform, float z)
        {
            var pos = gameObjectTransform.position;
            pos.z = z;
            gameObjectTransform.position = pos;
        }

        async UniTask RemoveConsumedItem(PlacedItemView consumedItemView)
        {
            await consumedItemView.RemovePlaceItemView(consumedItemView.gameObject.GetCancellationTokenOnDestroy());
            Destroy(consumedItemView.gameObject);
        }

        async UniTask LoadPrefabs(CancellationToken cancellationToken)
        {
            ReleaseLoadedPrefabs();

            await _playerOutpostPrefabReference.LoadAssetAsync<GameObject>().WithCancellation(cancellationToken);
            var playerOutpostGameObject = (GameObject)_playerOutpostPrefabReference.Asset;
            _playerOutpostPrefab = playerOutpostGameObject.GetComponent<OutpostView>();

            await _enemyOutpostPrefabReference.LoadAssetAsync<GameObject>().WithCancellation(cancellationToken);
            var enemyOutpostGameObject = (GameObject)_enemyOutpostPrefabReference.Asset;
            _enemyOutpostPrefab = enemyOutpostGameObject.GetComponent<OutpostView>();

            await _characterUnitPrefabReference.LoadAssetAsync<GameObject>().WithCancellation(cancellationToken);
            var characterUnitGameObject = (GameObject)_characterUnitPrefabReference.Asset;
            _characterUnitPrefab = characterUnitGameObject.GetComponent<FieldUnitView>();

            await _specialUnitPrefabReference.LoadAssetAsync<GameObject>().WithCancellation(cancellationToken);
            var specialUnitGameObject = (GameObject)_specialUnitPrefabReference.Asset;
            _specialUnitPrefab = specialUnitGameObject.GetComponent<FieldSpecialUnitView>();

            await _defenseTargetViewPrefabReference.LoadAssetAsync<GameObject>().WithCancellation(cancellationToken);
            var defenseTargetGameObject = (GameObject)_defenseTargetViewPrefabReference.Asset;
            _defenseTargetViewPrefab = defenseTargetGameObject.GetComponent<DefenseTargetView>();

            await _gimmickObjectViewPrefabReference.LoadAssetAsync<GameObject>().WithCancellation(cancellationToken);
            var gimmickObjectGameObject = (GameObject)_gimmickObjectViewPrefabReference.Asset;
            _gimmickObjectViewPrefab = gimmickObjectGameObject.GetComponent<InGameGimmickObjectView>();

            await _placeItemPrefabReference.LoadAssetAsync<GameObject>().WithCancellation(cancellationToken);
            var placedItemGameObject = (GameObject)_placeItemPrefabReference.Asset;
            _placedItemViewPrefab = placedItemGameObject.GetComponent<PlacedItemView>();
        }

        void ReleaseLoadedPrefabs()
        {
            if (_playerOutpostPrefabReference.IsValid()) _playerOutpostPrefabReference.ReleaseAsset();
            if (_enemyOutpostPrefabReference.IsValid()) _enemyOutpostPrefabReference.ReleaseAsset();
            if (_characterUnitPrefabReference.IsValid()) _characterUnitPrefabReference.ReleaseAsset();
            if (_specialUnitPrefabReference.IsValid()) _specialUnitPrefabReference.ReleaseAsset();
            if (_defenseTargetViewPrefabReference.IsValid()) _defenseTargetViewPrefabReference.ReleaseAsset();
            if (_gimmickObjectViewPrefabReference.IsValid()) _gimmickObjectViewPrefabReference.ReleaseAsset();
            if (_placeItemPrefabReference.IsValid()) _placeItemPrefabReference.ReleaseAsset();

            _playerOutpostPrefab = null;
            _enemyOutpostPrefab = null;
            _characterUnitPrefab = null;
            _specialUnitPrefab = null;
            _defenseTargetViewPrefab = null;
            _gimmickObjectViewPrefab = null;
            _placedItemViewPrefab = null;
        }
    }
}
