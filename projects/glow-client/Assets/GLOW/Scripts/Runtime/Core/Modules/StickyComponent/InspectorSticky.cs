using System;
using UnityEngine;

#pragma warning disable CS0414 // フィールドは割り当てられていますがその値は使用されていません

namespace GLOW.Core.Modules.StickyComponent
{
    [Serializable]
    public class EncryptedStickyData
    {
        [SerializeField] string _encryptedText = string.Empty;
        
        public string EncryptedText
        {
            get => _encryptedText;
            set => _encryptedText = value;
        }
    }
    
    public class InspectorSticky : MonoBehaviour
    {
        // ReSharper disable once NotAccessedField.Local
        [SerializeField] EncryptedStickyData _stickyData = new EncryptedStickyData();
    }
}
