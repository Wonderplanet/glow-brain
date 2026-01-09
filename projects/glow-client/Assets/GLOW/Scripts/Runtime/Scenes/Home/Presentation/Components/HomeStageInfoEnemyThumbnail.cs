using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.Home.Presentation.Components
{
    public class HomeStageInfoEnemyThumbnail : UIObject
    {
        [SerializeField] UIImage _enemyFrameImage;
        [SerializeField] UIImage _enemyIconImage;
        [SerializeField] UIText _enemyNameText;
        [SerializeField] CharacterColorIcon _colorIcon;
        [SerializeField] CharaRoleIcon _enemyRoleIcon;
        [SerializeField] UIImage _enemyStrongIcon;

        [SerializeField] Sprite[] _enemyRarityFrameSprites = {};

        public void Setup(
            CharacterColor characterColor,
            CharacterUnitKind characterUnitKind,
            EnemyCharacterIconAssetPath enemyCharacterIconAssetPath,
            CharacterName enemyName,
            CharacterUnitRoleType characterUnitRoleType)
        {
            SetColorIcon(characterColor);
            SetThumbnailFrame(characterUnitKind);
            SetEnemyThumbnailIcon(enemyCharacterIconAssetPath);
            SetEnemyName(enemyName);
            SetEnemyRoleIcon(characterUnitRoleType);
            SetStrongIcon(characterUnitKind);
        }

        void SetColorIcon(CharacterColor color)
        {
            _colorIcon.SetupCharaColorIcon(color);
        }

        void SetThumbnailFrame(CharacterUnitKind characterUnitKind)
        {
            _enemyFrameImage.Sprite = characterUnitKind == CharacterUnitKind.Normal ? _enemyRarityFrameSprites[0] : _enemyRarityFrameSprites[1];
        }

        void SetEnemyThumbnailIcon(EnemyCharacterIconAssetPath enemyCharacterIconAssetPath)
        {
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(
                _enemyIconImage.Image,
                enemyCharacterIconAssetPath.Value,
                () =>
                {
                    if (!_enemyIconImage) return;
                    _enemyIconImage.Image.SetNativeSize();
                });
        }

        void SetEnemyName(CharacterName enemyName)
        {
            _enemyNameText.SetText(enemyName.Value);
        }

        void SetEnemyRoleIcon(CharacterUnitRoleType characterUnitRoleType)
        {
            _enemyRoleIcon.SetupCharaRoleIcon(characterUnitRoleType);
        }

        void SetStrongIcon(CharacterUnitKind characterUnitKind)
        {
            _enemyStrongIcon.Hidden = characterUnitKind == CharacterUnitKind.Normal;
        }
    }
}
