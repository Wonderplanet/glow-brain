using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.ScriptableObjects;
using GLOW.Scenes.InGame.Presentation.Field;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UnityEngine;
using UnityEngine.Experimental.Rendering;

namespace GLOW.Scenes.EncyclopediaUnitDetail.Presentation.Views
{
    public class SpecialAttackPreviewUnitRenderComponent : MonoBehaviour
    {
        [SerializeField] Camera _camera;
        [SerializeField] Transform _unitRootCenter;
        [SerializeField] Transform _unitRootRight;
        [SerializeField] BattleEffectManager _battleEffectManager;

        UnitImage _unit;
        RenderTexture _renderTexture;

        public RenderTexture RenderTexture => _renderTexture;

        void Awake()
        {
            InitializeRenderTexture();
        }

        void OnDestroy()
        {
            _camera.targetTexture = null;
            _renderTexture?.Release();
        }

        public void BuildUnit(UnitImage unitImage, CharacterColor unitColor, IsEncyclopediaSpecialAttackPositionRight isRight)
        {
            if (null != _unit)
            {
                Destroy(_unit.gameObject);
            }

            Transform root = isRight ? _unitRootRight : _unitRootCenter;
            _unit = Instantiate(unitImage, root);
            _unit.SetUnitColor(CharacterColor.Colorless);
        }

        public void PlayAnimation(CharacterUnitAnimation animation)
        {
            _unit.StartAnimation(animation, CharacterUnitAnimation.Wait);
        }

        public BaseBattleEffectView PlayAttackEffect(UnitAttackViewInfo attackViewInfo)
        {
            if (attackViewInfo.AttackEffect != null)
            {
                return _battleEffectManager.Generate(attackViewInfo.AttackEffect, _unit.EffectRoot, new Vector3(0,0,0))
                    .BindCharacterImage(_unit)
                    .Play();
            }

            return null;
        }

        void InitializeRenderTexture()
        {
            _renderTexture = new RenderTexture(720, 540, 24, GraphicsFormat.R8G8B8A8_UNorm);
            _renderTexture.name = "EncyclopediaSpecialAttackUnitRenderComponent";
            _camera.targetTexture = _renderTexture;
        }
    }
}
