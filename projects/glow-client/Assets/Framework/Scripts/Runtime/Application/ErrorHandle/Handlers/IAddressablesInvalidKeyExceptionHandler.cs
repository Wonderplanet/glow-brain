using System;
using UnityEngine.AddressableAssets;

namespace WPFramework.Application.ErrorHandle
{
    public interface IAddressablesInvalidKeyExceptionHandler
    {
        bool Handle(InvalidKeyException exception, Action completion);
    }
}
