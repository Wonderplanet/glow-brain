using UnityEngine;

public class URColorTrigger : MonoBehaviour
{
    [SerializeField] Animator _animator;

    public void PlayTrigger(string triggerName)
    {
        if (_animator == null)
        {
            Debug.LogWarning("Animator が設定されていません");
            return;
        }

        _animator.SetTrigger(triggerName);
    }
}
