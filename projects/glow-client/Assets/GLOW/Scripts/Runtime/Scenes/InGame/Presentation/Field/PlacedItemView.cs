using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGame.Presentation.Common;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    public class PlacedItemView : MonoBehaviour
    {
        [SerializeField] SpriteRenderer _iconSpriteRenderer;
        [SerializeField] Animator _animator;
        [SerializeField] Sprite _damageIconSprite;
        [SerializeField] Sprite _healIconSprite;
        
        const float DefaultYPosition = 0.1f;
        const string PlayerOutAnimationName = "051-02-EffObject_A_Out";
        
        const string EnemyInAnimationName = "051-02-EffObject_B_in";
        const string EnemyOutAnimationName = "051-02-EffObject_B_Out";
        
        readonly MultipleSwitchController _pauseController = new ();
        
        public FieldObjectId Id { get; private set; }
        
        BattleSide _placedItemBattleSide = BattleSide.Player;
        
        protected virtual void Awake()
        {
            _pauseController.OnStateChanged = OnPause;
        }
        
        public void InitializePlaceItemView(
            PlacedItemModel placedItem,
            IViewCoordinateConverter viewCoordinateConverter,
            BattleStateEffectViewManager battleStateEffectViewManager)
        {
            Id = placedItem.PlacedItemId;
            
            var myTransform = transform;
            var pos = viewCoordinateConverter.ToFieldViewCoord(placedItem.Pos);
            myTransform.localPosition = new Vector3(pos.X, DefaultYPosition, 0.0f);
            
            var pickUpAttackElement = placedItem.PickUpAttackElement;
            if (pickUpAttackElement.IsEmpty()) return;
            
            _placedItemBattleSide = placedItem.PlacedItemBattleSide;
            if (_placedItemBattleSide == BattleSide.Enemy)
            {
                _animator.Play(EnemyInAnimationName);
            }

            if (pickUpAttackElement.AttackDamageType == AttackDamageType.Damage)
            {
                _iconSpriteRenderer.sprite = _damageIconSprite;
            }
            else if(pickUpAttackElement.AttackDamageType == AttackDamageType.Heal)
            {
                _iconSpriteRenderer.sprite = _healIconSprite;
            }
            else
            {
                var viewData = battleStateEffectViewManager
                    .GetStateEffectViewData(pickUpAttackElement.StateEffect.Type);
                if (viewData == null || viewData.Icon == null) return;
            
                _iconSpriteRenderer.sprite = viewData.Icon;
            }
        }

        public async UniTask RemovePlaceItemView(CancellationToken cancellationToken)
        {
            if (_placedItemBattleSide == BattleSide.Enemy)
            {
                _animator.Play(EnemyOutAnimationName);
                
            }
            else
            {
                _animator.Play(PlayerOutAnimationName);
            }
            
            await UniTask.WaitUntil(
                () =>  _animator.GetCurrentAnimatorStateInfo(0).normalizedTime >= 1.0f, 
                cancellationToken: cancellationToken);
        }

        public MultipleSwitchHandler Pause(MultipleSwitchHandler handler)
        {
            return _pauseController.TurnOn(handler);
        }
        
        void OnPause(bool isPause)
        {
            if(_animator != null)
            {
                _animator.speed = isPause ? 0f : 1f;
            }
        }
    }
}