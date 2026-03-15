using System;
using Cysharp.Threading.Tasks;

namespace GLOW.Modules.Tutorial.Domain.Context
{
    public interface IReleaseHardTutorialContext
    {
        UniTask<bool> DoIfTutorial(Func<UniTask> action);
    }
}