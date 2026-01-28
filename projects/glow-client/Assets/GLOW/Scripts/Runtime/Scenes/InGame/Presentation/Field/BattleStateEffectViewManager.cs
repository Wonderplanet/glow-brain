using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Presentation.Data;
using UnityEngine;
using UnityEngine.AddressableAssets;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    public class BattleStateEffectViewManager : MonoBehaviour
    {
        [SerializeField] AssetReference _battleEffectStateEffectViewInfoListReference;
        
        StateEffectViewDataList _battleEffectStateEffectViewInfoList;
        
        void OnDestroy()
        {
            ReleaseStateEffectIconInfoList();
        }
        
        public async UniTask Initialize(CancellationToken cancellationToken)
        {
            await LoadStateEffectViewInfoList(cancellationToken);
        }
        
        public StateEffectViewData GetStateEffectViewData(StateEffectType stateEffectType)
        {
            if (_battleEffectStateEffectViewInfoList == null) return null;
            
            var data = _battleEffectStateEffectViewInfoList.List
                .Find(x => x.StateEffectType == stateEffectType);
            
            return data;
        }
        
        async UniTask LoadStateEffectViewInfoList(CancellationToken cancellationToken)
        {
            ReleaseStateEffectIconInfoList();

            await _battleEffectStateEffectViewInfoListReference
                .LoadAssetAsync<StateEffectViewDataList>()
                .WithCancellation(cancellationToken);
                
            _battleEffectStateEffectViewInfoList = (StateEffectViewDataList)_battleEffectStateEffectViewInfoListReference.Asset;
        }
        
        void ReleaseStateEffectIconInfoList()
        {
            if (_battleEffectStateEffectViewInfoListReference.IsValid())
            {
                _battleEffectStateEffectViewInfoListReference.ReleaseAsset();
            }
            
            _battleEffectStateEffectViewInfoList = null;
        }
    }
}