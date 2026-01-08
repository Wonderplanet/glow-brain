using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGame.Presentation.Common;
using UnityEngine;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    public class InGameGimmickObjectView : MonoBehaviour
    {
        const float DefaultYPosition = 0.6f;

        [SerializeField] GameObject _imageRoot;

        [Inject] IInGameGimmickObjectImageContainer InGameGimmickObjectImageContainer { get; }
        [Inject] BattleEffectManager BattleEffectManager { get; }

        InGameGimmickObjectImage _inGameGimmickObjectImage;

        public FieldObjectId Id { get; private set; }
        public AutoPlayerSequenceElementId AutoPlayerSequenceElementId { get; private set; }

        public void Initialize(
            InGameGimmickObjectModel inGameGimmickObjectModel,
            IViewCoordinateConverter viewCoordinateConverter)
        {
            Id = inGameGimmickObjectModel.Id;
            AutoPlayerSequenceElementId = inGameGimmickObjectModel.AutoPlayerSequenceElementId;

            var myTransform = transform;
            var pos = viewCoordinateConverter.ToFieldViewCoord(BattleSide.Enemy, inGameGimmickObjectModel.Pos);
            myTransform.localPosition = new Vector3(pos.X, DefaultYPosition, 0.0f);

            var scale = myTransform.localScale;
            myTransform.localScale = new Vector3(scale.x, scale.y, scale.z * 0.1f);

            var imagePrefab = InGameGimmickObjectImageContainer.Get(inGameGimmickObjectModel.AssetKey);
            _inGameGimmickObjectImage = Instantiate(imagePrefab, _imageRoot.transform, false)
                .GetComponent<InGameGimmickObjectImage>();
        }

        public BaseBattleEffectView OnTransformEffect()
        {
            Vector3 setPos = _inGameGimmickObjectImage.EffectRoot.position;
            setPos.z = transform.position.z;
            var effectView = BattleEffectManager
                .Generate(BattleEffectId.TransformGimmickObjectToEnemy, setPos)
                ?.Play();

            if (effectView != null)
            {
                var setScale = effectView.transform.localScale * _inGameGimmickObjectImage.TransformToEnemyEffectScale;
                effectView.transform.localScale = setScale;
            }

            return effectView;
        }
    }
}
